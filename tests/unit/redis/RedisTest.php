<?php

namespace Test\unit\redis;

use Test\GlobalTest;

final class RedisTest extends GlobalTest {
  public function testRedisConstructor(): void
  {
    // Use the Redis host/port configured in test.ini
    $host = ini_get('safeservicewrapper.redis_host');
    $port = ini_get('safeservicewrapper.redis_port');

    // Create a Redis instance
    $redis = new \SafeServiceWrapper\Redis(['host' => $host, 'port' => $port]);
    $this->assertInstanceOf('Redis', $redis, "Constructor should return instance of Redis class");

    // Perform basic operations
    $setResult = $redis->set('phpunit_key_constructor', 'value1');
    $this->assertTrue($setResult, "SET command should return true on success.");

    $value = $redis->get('phpunit_key_constructor');

    $this->assertSame($value, 'value1', "GET command should retrieve the correct value.");

    $redis->close();
  }

  public function testRedisConnectMethod(): void
  {
    // Use the Redis host/port configured in test.ini
    $host = ini_get('safeservicewrapper.redis_host');
    $port = ini_get('safeservicewrapper.redis_port');

    $redis = new \SafeServiceWrapper\Redis();
    $this->assertInstanceOf( 'Redis', $redis,"Constructor should return instance of Redis class");

    // Connect using the method
    $connected = $redis->connect($host, $port);
    $this->assertTrue($connected, "connect() method should return true on success.");

    // Perform basic operations
    $setResult = $redis->set('phpunit_key_method', 'value2');
    $this->assertTrue($setResult, "SET command should return true on success.");

    $value = $redis->get('phpunit_key_method');
    $this->assertSame($value, 'value2', "GET command should retrieve the correct value.");

    $redis->close();
  }
}
