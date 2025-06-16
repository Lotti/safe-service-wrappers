<?php

namespace Test\unit\redis;

use Test\GlobalTest;

final class RedisTest extends GlobalTest {
  public function testRedisSessionHandler(): void {
    \SafeServiceWrapper\Redis::configureSessionHandling();
    $session_handler = ini_get('session.save_handler');
    $session_path = ini_get('session.save_path');
    $this->assertEquals('tcp://127.0.0.1:6379?weight=1&persistent=0&prefix=PHPREDIS_SESSION%3A&auth[]=test&auth[]=test1234&database=0', $session_path);
    $this->assertEquals('redis', $session_handler);
  }
  public function testRedisConstructor(): void
  {
    // Use the Redis host/port configured in test.ini
    $host = getenv('VALKEY_HOST');
    $port = getenv('VALKEY_PORT');

    // Create a Redis instance
    $redis = new \SafeServiceWrapper\Redis(['host' => $host, 'port' => intval($port), 'auth'=>['test']]);
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
    $host = getenv('VALKEY_HOST');
    $port = getenv('VALKEY_PORT');

    $redis = new \SafeServiceWrapper\Redis();
    $this->assertInstanceOf( 'Redis', $redis,"Constructor should return instance of Redis class");

    // Connect using the method
    $connected = $redis->connect($host, intval($port));
    $this->assertTrue($connected, "connect() method should return true on success.");

    $redis->auth('test');

    // Perform basic operations
    $setResult = $redis->set('phpunit_key_method', 'value2');
    $this->assertTrue($setResult, "SET command should return true on success.");

    $value = $redis->get('phpunit_key_method');
    $this->assertSame($value, 'value2', "GET command should retrieve the correct value.");

    $redis->close();
  }
}
