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
 * Expiration min heap.
 *
 * @author Eduardo Oliveira <entering@gmail.com>
 */
class ExpirationMinHeap extends \SplHeap
{
    /**
     * Compare.
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    public function compare($array1, $array2)
    {
        $value1 = current($array1);
        $value2 = current($array2);

        if ($value1 == $value2) {
            return 0;
        }

        return ($value1 > $value2) ? -1 : 1;
    }
}
