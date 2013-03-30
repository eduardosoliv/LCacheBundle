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

/**
 * Cache interface.
 *
 * @author Eduardo Oliveira <entering@gmail.com>
 */
interface CacheInterface
{
    /**
     * Get by key.
     *
     * @param integer|float|string|boolean $key Key.
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public function get($key);

    /**
     * Set a value in cache.
     *
     * @param integer|float|string|boolean $key        Key.
     * @param mixed                        $value      Value.
     * @param null|integer                 $expiration Expiration in seconds (if null will not expire).
     *
     * @throws \InvalidArgumentException
     */
    public function set($key, $value, $expiration = null);

    /**
     * Get not found.
     *
     * @return boolean True if a get failed because key not found, false otherwise.
     */
    public function notFound();

    /**
     * Return stats.
     *
     * @return array
     */
    public function getStats();
}
