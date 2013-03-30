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
 * Introspectable interface.
 *
 * @author Eduardo Oliveira <entering@gmail.com>
 */
interface IntrospectableInterface
{
    /**
     * Return stats.
     *
     * @return array
     */
    public function getStats();

    /**
     * Get all keys on the cache, expired too, this method is just for debug
     * purpose.
     *
     * @return array
     */
    public function getAllKeys();
}
