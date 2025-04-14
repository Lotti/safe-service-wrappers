<?php

use SafeServiceWrapper\Redis;

uses()->group('redis');

it('connects via constructor and performs basic commands', function () {
    // The Redis wrapper should read host/port from the updated test.ini
    // We don't need to pass host/port explicitly if the wrapper reads INI
    // Assuming the default container has no password
    $redis = new Redis(); // Assumes constructor reads INI or has defaults

    expect($redis)->toBeInstanceOf(Redis::class);

    // Perform basic operations
    $setResult = $redis->set('phpunit_key_constructor', 'value1');
    expect($setResult)->toBeTrue("SET command should return true on success.");

    $value = $redis->get('phpunit_key_constructor');
    expect($value)->toBe('value1', "GET command should retrieve the correct value.");

    $redis->close();
});

it('connects via method and performs basic commands', function () {
    $redis = new Redis();
    expect($redis)->toBeInstanceOf(Redis::class);

    // Use the network alias and internal port configured by IntegrationTestCase via test.ini
    // The wrapper should read these from INI. If not, we'd pass them here.
    // $connected = $redis->connect(self::$redisHost, self::$redisPort); // Use if wrapper doesn't read INI
    // Assuming connect() without args reads from INI:
    $connected = $redis->connect();
    expect($connected)->toBeTrue("connect() method should return true on success.");

    // Perform basic operations
    $setResult = $redis->set('phpunit_key_method', 'value2');
    expect($setResult)->toBeTrue("SET command should return true on success.");

    $value = $redis->get('phpunit_key_method');
    expect($value)->toBe('value2', "GET command should retrieve the correct value.");

    $redis->close();
});
