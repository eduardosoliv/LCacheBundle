<?php

/*
 * This file is a part of the PHP Local Cache Bundle.
 *
 * (c) 2013 Eduardo Oliveira
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ESO\LCacheBundle\Tests\Cache;

use ESO\LCacheBundle\Cache\ExpirationMinHeap;

/**
 * Expiration min heap tests.
 *
 * @author Eduardo Oliveira <entering@gmail.com>
 */
class ExpirationMinHeapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Expiration min heap insteance.
     *
     * @var ExpirationMinHeap
     */
    private $expirationMinHeap;

    /**
     * Set up.
     *
     * Before a test is run, setUp() is invoked.
     *
     * {@see http://www.phpunit.de/manual/current/en/fixtures.html}
     */
    protected function setUp()
    {
        $this->expirationMinHeap = new ExpirationMinHeap();
    }

    /**
     * Test order.
     */
    public function testOrder()
    {
        $items = array(
            array('test1' => 200),
            array('test2' => 100),
            array('test3' => 200),
            array('test4' => 150),
            array('test5' => 40),
            array('test6' => 300),
        );

        foreach ($items as $item) {
            $this->expirationMinHeap->insert($item);
        }

        $this->expirationMinHeap->top();

        $itemsHeap = array();
        while ($this->expirationMinHeap->valid()) {
            $itemsHeap[] = $this->expirationMinHeap->current();
            $this->expirationMinHeap->next();
        }
        $this->assertCount(count($items), $items);

        $lastItemValue = 0;
        foreach ($itemsHeap as $item) {
            $this->assertGreaterThanOrEqual(
                $lastItemValue,
                $value = current($item)
            );
            $lastItemValue = $value;
        }
    }
}
