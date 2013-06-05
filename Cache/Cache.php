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
 * Handles expiration in a lazy way, when a key is fetched if it is expired
 * will be unset and a miss simulated.
 *
 * It also handle expiration on a more actively way, every set is checked if
 * there is a item to be expired if so it is expired, this is done keeping an
 * auxiliary min heap structure.
 *
 * Take in consideration that you should be careful using this class directly as
 * a symfony service, because it will be a singleton.
 *
 * Probably the best way to use this cache is to extend it and use the extended
 * class, that way you ensure that you get a instance just for yourself and you
 * make easy to add specific behaviors on the future.
 *
 * @author Eduardo Oliveira <entering@gmail.com>
 */
class Cache implements CacheInterface, IntrospectableInterface
{
    /**
     * Key.
     */
    const KEY = 'key';

    /**
     * Key separator.
     */
    const KEY_SEPARATOR = ':';

    /**
     * Value.
     */
    const KEY_VALUE = 'value';

    /**
     * Expiration at.
     */
    const KEY_EXPIRATION_AT = 'expiration_at';

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
     * Expiration min heap.
     *
     * @var ExpirationMinHeap
     */
    protected $expirationHeap;

    /**
     * Prefix.
     *
     * @var string
     */
    protected $prefix = '';

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
     * Number of times delete command was called.
     *
     * @var int
     */
    protected $cmdDel = 0;

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
        $this->expirationHeap = new ExpirationMinHeap();
    }

    /**
     * Set a prefix of keys names.
     *
     * @param string $prefix Prefix.
     */
    public function setPrefix($prefix)
    {
        InvalidArgumentException::requireString($prefix, __METHOD__, 1);

        $this->prefix = ($prefix == '')
            ? ''
            : $prefix . static::KEY_SEPARATOR;
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

        // add prefix to key
        $key = $this->prefix . $key;

        if (isset($this->cache[$key])) {
            // check expiration
            if ($this->cache[$key][static::KEY_EXPIRATION_AT] !== null &&
                $this->cache[$key][static::KEY_EXPIRATION_AT] < (new \DateTime())->getTimestamp()
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

        // try to expire
        if ($this->expirationHeap->valid()) {
            $top = $this->expirationHeap->top();
            if (current($top) < (new \DateTime())->getTimestamp()) {
                // expire
                unset($this->cache[key($top)]);
                $this->expirationHeap->extract();
            }
        }

        // validate data
        InvalidArgumentException::requireScalar($key, __METHOD__, 1);
        InvalidArgumentException::optionalPositiveInteger($expiration, __METHOD__, 3);

        // add prefix to key
        $key = $this->prefix . $key;

        // set key on cache
        $expirationAt = ($expiration === null)
            ? null
            : ((new \DateTime())->getTimestamp() + $expiration);
        $this->cache[$key] = array(
            static::KEY_VALUE => $value,
            static::KEY_EXPIRATION_AT => $expirationAt
        );

        // add to expiration heap, if it have expiration at
        if ($expirationAt !== null) {
            $this->expirationHeap->insert(array($key => $expirationAt));
        }
    }

    /**
     * Deletes a value from cache given the key.
     *
     * @param string $key Key
     */
    public function del($key)
    {
        // increment stats
        ++$this->cmdDel;

        unset($this->cache[$key]);
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
            'cmd_del' => $this->cmdDel,
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
