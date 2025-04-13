<?php

$ini_file = __DIR__.'/test.ini';
// Load and apply settings from test.ini
if (file_exists($ini_file)) {
    $settings = parse_ini_file($ini_file, true);
    if (isset($settings['safeservicewrapper'])) {
        foreach ($settings['safeservicewrapper'] as $key => $value) {
            ini_set("safeservicewrapper.$key", $value);
            // Uncomment to debug:
            // echo "Setting safeservicewrapper.$key = $value\n";
        }
    } else {
        echo "Warning: No [safeservicewrapper] section found in test.ini\n";
    }
} else {
    echo "Warning: test.ini file not found\n";
}

// Configure Redis session handling (optional, uncomment if needed)
// \SafeServiceWrapper\Redis::configureSessionHandling();

// Test 1: Using constructor with options

$credentials = [
  ['test', null],
  ['test', null],
  '',
  '',
];

foreach ($credentials as $k => $auth) {
  echo "TEST #".($k+1)." credentials:  ".var_export($auth,true). ":\n";
  
  try {
    $redis = new \SafeServiceWrapper\Redis(['host'=>'127.0.0.1', 'port'=>6379, 'auth'=> $auth]);
      echo "Successfully connected to Redis\n";

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
      $redis->auth($auth);

      $redis->set('mykey', 'Hello Redis!');
      $value = $redis->get('mykey');
      echo "Value from Redis: " . $value . "\n";
      $redis->close();
      assert($value === 'Hello Redis!');
  } catch (\Exception $e) {
      echo "Error: " . $e->getMessage() . "\n";
  }
  echo "--------\n";
}