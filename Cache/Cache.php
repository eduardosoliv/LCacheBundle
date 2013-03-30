<?php

/*
 * This file is a part of the PHP Local Cache Bundle.
 *
 * (c) 2013 Eduardo Oliveira
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ESO\LCacheBundle\Cache;

use ESO\LCacheBundle\Exception\InvalidArgumentException;

/**
 * Cache.
 *
 * Simple class to cache data.
 *
 * It handle expiration in a lazy way, when a key is fetched if it is expired
 * will be unset and a miss simulated.
 *
 * It also handle expiration on a more actively way, every set is checked if
 * there is a item to be expired it is expired, this is done keeping an
 * auxiliary queue structure, making very efficient the expiration.
 *
 * Take in consideration that you should be careful using this class directly as
 * a symfony service, because it will be a singleton, and you can get
 * unpredictable behaviors.
 *
 * Probably the best way to use this cache is to extend it and use the extended
 * class, that way you ensure that you get a instance just for yourself and you
 * make easy to add specific behaviors on the future.
 *
 * @author Eduardo Oliveira <entering@gmail.com>
 */
class Cache implements CacheInterface
{
    /**
     * Key.
     */
    const KEY = 'key';

    /**
     * Stored at.
     */
    const KEY_STORED_AT = 'stored_at';

    /**
     * Value.
     */
    const KEY_VALUE = 'value';

    /**
     * Expiration.
     */
    const KEY_EXPIRATION = 'expiration';

    /**
     * Cache array, structure:
     * array(
     *     KEY => array(
     *         'stored_at' => timestamp,
     *         'hits' => number,
     *         'value' => VALUE
     *     ),
     *     ...
     * )
     *
     * @var array
     */
    protected $cache = array();

    /**
     * Cache queue, used to expire actively keys.
     *
     * @var \SplQueue
     */
    protected $cacheQueue;

    /**
     * If true some get failed.
     *
     * @var boolean
     */
    protected $notFound = false;

    // stats data

    /**
     * Number of times get command was called.
     *
     * @var integer
     */
    protected $cmdGet = 0;

    /**
     * Number of times set command was called.
     *
     * @var integer
     */
    protected $cmdSet = 0;

    /**
     * Get hits.
     *
     * @var integer
     */
    protected $getHits = 0;

    /**
     * Get misses.
     *
     * @var integer
     */
    protected $getMisses = 0;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->cacheQueue = new \SplQueue();
    }

    /**
     * Get by key.
     *
     * @param integer|float|string|boolean $key Key.
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function get($key)
    {
        // increment stats
        ++$this->cmdGet;

        // replace variables state
        $this->notFound = false;

        // validate arguments
        InvalidArgumentException::requireScalar($key, __METHOD__, 1);

        if (isset($this->cache[$key])) {
            // check expiration
            if ($this->cache[$key][static::KEY_EXPIRATION] !== null &&
                $this->cache[$key][static::KEY_STORED_AT] + $this->cache[$key][static::KEY_EXPIRATION]
                < (new \DateTime())->getTimestamp()
            ) {
                // simulate a miss
                unset($this->cache[$key]);
            } else {
                ++$this->getHits;
                return $this->cache[$key][static::KEY_VALUE];
            }
        }

        // is a miss
        ++$this->getMisses;
        $this->notFound = true;

        return false;
    }

    /**
     * Set a value in cache.
     *
     * @param integer|float|string|boolean $key        Key.
     * @param mixed                        $value      Value.
     * @param null|integer                 $expiration Expiration in seconds (if null will not expire).
     *
     * @throws \InvalidArgumentException
     */
    public function set($key, $value, $expiration = null)
    {
        // increment stats
        ++$this->cmdSet;

        // validate data
        InvalidArgumentException::requireScalar($key, __METHOD__, 1);
        InvalidArgumentException::optionalPositiveInteger($expiration, __METHOD__, 3);

        // check if the last item on queue can be expire
        if (!$this->cacheQueue->isEmpty()) {
            $top = $this->cacheQueue->top();
            if ($top[static::KEY_STORED_AT] + $top[static::KEY_EXPIRATION] <
                (new \DateTime())->getTimestamp()
            ) {
                unset($this->cache[$top[static::KEY]]);
                $this->cacheQueue->pop();
            }
        }

        // set key on cache
        $storedAt = (new \DateTime())->getTimestamp();
        $this->cache[$key] = array(
            static::KEY_STORED_AT => $storedAt,
            static::KEY_VALUE => $value,
            static::KEY_EXPIRATION => $expiration
        );

        // add to queue to make the actively expire easier
        if ($expiration !== null) {
            $this->cacheQueue->push(
                array(
                    static::KEY => $key,
                    static::KEY_STORED_AT => $storedAt,
                    static::KEY_EXPIRATION => $expiration
                )
            );
        }
    }

    /**
     * Get not found.
     *
     * @return boolean True if a get failed because key not found, false otherwise.
     */
    public function notFound()
    {
        return $this->notFound;
    }

    /**
     * Return stats.
     *
     * @return array
     */
    public function getStats()
    {
        return array(
            'curr_items' => count($this->cache),
            'cmd_get' => $this->cmdGet,
            'cmd_set' => $this->cmdSet,
            'get_hits' => $this->getHits,
            'get_misses' => $this->getMisses,
        );
    }

    /**
     * Get all keys on the cache, expired too, this method is just for debug
     * purpose.
     *
     * @return array
     */
    public function getAllKeys()
    {
        return array_keys($this->cache);
    }
}
