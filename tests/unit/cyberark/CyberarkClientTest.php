<?php

namespace Test\unit\cyberark;

use Test\GlobalTest;

final class CyberarkClientTest extends GlobalTest {
  public function testFetchPasswordFromMockServer(): void {
    // Define expected inputs that the mock server should respond to
    $host = 'targethost.example.com';
    $port = 1234;
    $user = 'targetuser';

    // Expected password from mock_cyberark_server.php for these inputs
    $expectedPassword = 'default1234';

    $result = \SafeServiceWrapper\CyberarkClient::fetchPassword($host, $port, $user);

    $this->assertIsArray($result, "Must be an array");
    $this->assertArrayHasKey('password', $result, "Array must have password key");
    $this->assertArrayHasKey('cache_hit', $result, "Array must have cache_hit key");
    $this->assertSame($result['password'], $expectedPassword, "Password should match configured one");
  }

  public function testFetchPasswordWithCacheEnabled(): void {
    // Define expected inputs
    $host = 'cachehost.example.com';
    $port = 5678;
    $user = 'cacheuser';
    $expectedPassword = 'default1234';

    // Ensure cache TTL is positive in test.ini
    $cacheTtl = (int) ini_get('safeservicewrapper.cache_ttl');
    $this->assertGreaterThan(0, $cacheTtl, "Cache TTL must be > 0 for this test in test.ini");

    // First call (should be cache miss or fill cache)
    $result1 = \SafeServiceWrapper\CyberarkClient::fetchPassword($host, $port, $user);
    $this->assertSame($result1['password'], $expectedPassword, "Password should match configured one");
    $this->assertFalse($result1['cache_hit'], "Expected cache miss on first fetch");

    // Wait a moment, but less than TTL
    usleep(100000);

    // Second call (should be cache hit)
    $result2 = \SafeServiceWrapper\CyberarkClient::fetchPassword($host, $port, $user);
    $this->assertSame($result2['password'], $expectedPassword, "Password should match configured one");
    $this->assertFalse($result2['cache_hit'], "Expected cache hit on second fetch");
 }
}
