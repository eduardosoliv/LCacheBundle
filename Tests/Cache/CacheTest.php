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

use ESO\LCacheBundle\Cache\Cache;

/**
 * Cache tests.
 *
 * @author Eduardo Oliveira <entering@gmail.com>
 */
class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Cache instance.
     *
     * @var Cache
     */
    private $cache;

    /**
     * Set up.
     *
     * Before a test is run, setUp() is invoked.
     *
     * {@see http://www.phpunit.de/manual/current/en/fixtures.html}
     */
    protected function setUp()
    {
        $this->cache = new Cache();
    }

    /*
     |--------------------------------------------------------------------------
     | Test gets/sets.
     |--------------------------------------------------------------------------
     */

    /**
     * Test get key as array.
     *
     * @expectedException \InvalidArgumentException
     * @expectedException to be scalar
     */
    public function testGetKeyAsArray()
    {
        $this->cache->get(array('invalid-key'));
    }

    /**
     * Test get absent.
     */
    public function testGetAbsent()
    {
        $this->assertFalse($this->cache->get('absent-key'));
        $this->assertTrue($this->cache->notFound());
    }

    /**
     * Test set key as array.
     *
     * @expectedException \InvalidArgumentException
     * @expectedException to be scalar
     */
    public function testSetKeyAsArray()
    {
        $this->cache->set(array('invalid-key'), 'value');
    }

    /**
     * Test set key as array.
     *
     * @expectedException \InvalidArgumentException
     * @expectedException to be null or positive integer
     */
    public function testSetExpirationNegative()
    {
        $this->cache->set('key', 'value', -10);
    }

    /**
     * Test set and get.
     */
    public function testSetGet()
    {
        $key = 'test-key';
        $value = 'test-value';

        $this->cache->set($key, $value);
        $this->assertEquals($value, $this->cache->get($key));
    }

    /**
     * Test set and get with prefix
     */
    public function testSetGetWithPrefix()
    {
        $this->cache->setPrefix('test-prefix');
        $key = 'test-key';
        $value = 'test-value';

        $this->cache->set($key, $value);
        $this->assertEquals($value, $this->cache->get($key));
    }

    /**
     * Test set with expiration.
     */
    public function testSetExpiration()
    {
        $key = 'test-key';
        $value = 'test-value';

        $this->cache->set($key, $value, 1);
        $this->assertEquals($value, $this->cache->get($key));

        sleep(2);
        $this->assertFalse($this->cache->get($key));
        $this->assertTrue($this->cache->notFound());
    }

    /*
     |--------------------------------------------------------------------------
     | Test others.
     |--------------------------------------------------------------------------
     */

    /**
     * Test stats.
     */
    public function testsStats()
    {
        $gets = 10;
        $sets = 5;

        $statsEmpty = $this->cache->getStats();
        $this->assertInternalType('array', $statsEmpty);
        foreach ($statsEmpty as $stat) {
            $this->assertEquals(0, $stat);
        }

        for ($i = 1; $i <= $gets; ++$i) {
            $this->cache->get('absent-key');
        }

        for ($i = 1; $i <= $sets; ++$i) {
            $this->cache->set('key' . $i, 10);
        }

        for ($i = 1; $i <= $gets; ++$i) {
            $this->cache->get('key' . $i);
        }

        $stats = $this->cache->getStats();
        $this->assertEquals($sets, $stats['curr_items']);
        $this->assertEquals($gets * 2, $stats['cmd_get']);
        $this->assertEquals($sets, $stats['cmd_set']);
        $this->assertEquals($sets, $stats['get_hits']);
        $this->assertEquals($gets + $sets, $stats['get_misses']);
    }

    /**
     * Test actively expiration.
     */
    public function testActivelyExpiration()
    {
        $this->cache->set('key1-expire', 'value', 3);
        $this->cache->set('key2-expire', 'value', 1);
        $this->cache->set('key1-no-expire', 'value'); //no expiration
        // key1-expire, key2-expire and key1-no-expire
        $this->assertEquals(3, $this->cache->getStats()['curr_items']);
        $this->assertEquals(
            array('key1-expire', 'key2-expire', 'key1-no-expire'),
            $this->cache->getAllKeys()
        );

        sleep(2);

        // key1-expire, key2-expire and key1-no-expire
        $this->assertEquals(3, $this->cache->getStats()['curr_items']);
        $this->assertEquals(
            array('key1-expire', 'key2-expire', 'key1-no-expire'),
            $this->cache->getAllKeys()
        );

        $this->cache->set('key2-no-expire', 'value');

        // key1-expire, key1-no-expire and key2-no-expire
        $this->assertEquals(3, $this->cache->getStats()['curr_items']);
        $this->assertEquals(
            array('key1-expire', 'key1-no-expire', 'key2-no-expire'),
            $this->cache->getAllKeys()
        );

        // be sure that another set will not expire key1-expire
        $this->cache->set('key2-no-expire', 'value');
        $this->assertEquals(3, $this->cache->getStats()['curr_items']);
        $this->assertEquals(
            array('key1-expire', 'key1-no-expire', 'key2-no-expire'),
            $this->cache->getAllKeys()
        );

        sleep(2);
        $this->cache->set('key3-no-expire', 'value');

        // key1-no-expire and key2-no-expire and key3-no-expire
        $this->assertEquals(3, $this->cache->getStats()['curr_items']);
        $this->assertEquals(
            array('key1-no-expire', 'key2-no-expire', 'key3-no-expire'),
            $this->cache->getAllKeys()
        );
    }
}
