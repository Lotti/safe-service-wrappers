<?php

use SafeServiceWrapper\CyberarkClient;

uses()->group('cyberark');

it('fetches a password from the mock server', function () {
    // Define expected inputs that the mock server should respond to
    $host = 'targethost.example.com';
    $port = 1234;
    $user = 'targetuser';

    // Expected password from mock_cyberark_server.php for these inputs
    $expectedPassword = 'mock_password_for_' . $user . '@' . $host;

    $result = CyberarkClient::fetchPassword($host, $port, $user);

    expect($result)->toBeArray();
    expect($result)->toHaveKey('password');
    expect($result)->toHaveKey('cache_hit');
    expect($result['password'])->toBe($expectedPassword);
});

it('fetches a password with caching enabled', function () {
    // Define expected inputs
    $host = 'cachehost.example.com';
    $port = 5678;
    $user = 'cacheuser';
    $expectedPassword = 'mock_password_for_' . $user . '@' . $host;

    // Ensure cache TTL is positive in test.ini
    $cacheTtl = (int) ini_get('safeservicewrapper.cache_ttl');
    expect($cacheTtl)->toBeGreaterThan(0, "Cache TTL must be > 0 for this test in test.ini");

    // First call (should be cache miss or fill cache)
    $result1 = CyberarkClient::fetchPassword($host, $port, $user);
    expect($result1['password'])->toBe($expectedPassword);

    // Wait a moment, but less than TTL
    sleep(1);

    // Second call (should be cache hit)
    $result2 = CyberarkClient::fetchPassword($host, $port, $user);
    expect($result2['password'])->toBe($expectedPassword);
    expect($result2['cache_hit'])->toBeTrue("Expected cache hit on second fetch");
});
