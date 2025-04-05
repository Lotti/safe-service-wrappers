<?php

use SafeServiceWrapper\MyRedis;

$redis = new MyRedis();

try {
    $redis->connect('127.0.0.1', 6379);
    echo "Successfully connected to Redis\n";
    // $redis->auth('');

    $redis->set('mykey', 'Hello Redis!');
    $value = $redis->get('mykey');
    echo "Value from Redis: " . $value . "\n";

    echo "get auth: ".$redis->getAuth();

    $redis->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
