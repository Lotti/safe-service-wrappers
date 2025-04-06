<?php

try {
  $redis = new \SafeServiceWrapper\Redis(['host'=>'127.0.0.1', 'port'=>6379, 'auth'=>['test',null]]);
    echo "Successfully connected to Redis\n";
    echo "get auth: ".$redis->getAuth();

    $redis->set('mykey', 'Hello Redis!');
    $value = $redis->get('mykey');
    echo "Value from Redis: " . $value . "\n";
    $redis->close();
    assert($value === 'Hello Redis!');
} catch (\Exception $e) {
  echo "Error: " . $e->getMessage() . "\n";
}

try {
    $redis = new \SafeServiceWrapper\Redis();
    $redis->connect('127.0.0.1', 6379);
    echo "Successfully connected to Redis\n";
    echo "get auth: ".$redis->getAuth();
    $redis->auth('');

    $redis->set('mykey', 'Hello Redis!');
    $value = $redis->get('mykey');
    echo "Value from Redis: " . $value . "\n";
    $redis->close();
    assert($value === 'Hello Redis!');
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
