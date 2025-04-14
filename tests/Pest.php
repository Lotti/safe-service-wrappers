<?php

use Docker\Docker;
use Testcontainers\Container;
use Testcontainers\GenericContainer;
use Testcontainers\Network;
use Testcontainers\Wait;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

// uses(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/*
|--------------------------------------------------------------------------
| Test Environment Setup
|--------------------------------------------------------------------------
|
| Here you can define functions that run before and after your tests. This is where you can
| set up and tear down your test environment.
|
*/

// Define container properties (static)
dataset('containers', function () {
    return [
        'network' => null,
        'redisContainer' => null,
        'mariaDbContainer' => null,
        'mockCyberarkContainer' => null,
        'redisHost' => null,
        'redisPort' => null,
        'mariaDbHost' => null,
        'mariaDbPort' => null,
        'mariaDbUser' => 'testuser',
        'mariaDbPassword' => 'testpassword',
        'mariaDbDatabase' => 'testdb',
        'mockCyberarkHost' => null,
        'mockCyberarkPort' => 8080,
        'testIniPath' => __DIR__ . '/test.ini',
    ];
});

beforeAll(function () {
    // Extract container properties
    $network = null;
    $redisContainer = null;
    $mariaDbContainer = null;
    $mockCyberarkContainer = null;
    $redisHost = null;
    $redisPort = null;
    $mariaDbHost = null;
    $mariaDbPort = null;
    $mariaDbUser = 'testuser';
    $mariaDbPassword = 'testpassword';
    $mariaDbDatabase = 'testdb';
    $mockCyberarkHost = null;
    $mockCyberarkPort = 8080;
    $testIniPath = __DIR__ . '/test.ini';

    // Determine cache path and clear old cache
    $iniContent = @file_get_contents($testIniPath);
    if ($iniContent !== false) {
        preg_match('/^cache_path\s*=\s*"?([^"]*)"?/m', $iniContent, $matches);
        $cachePath = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : sys_get_temp_dir();
    } else {
        $cachePath = sys_get_temp_dir();
    }
    echo "Clearing cache files from: $cachePath\n";
    $cacheFiles = glob($cachePath . '/cyberark_cache_*');
    if ($cacheFiles) {
        foreach ($cacheFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    // Create a shared network for containers to communicate by hostname
    $network = Network::create();

    // Start Redis Container
    $redisContainer = GenericContainer::fromImage('redis:alpine')
        ->withExposedPort(6379)
        ->withNetwork($network) // Attach to network
        ->withNetworkAliases('redis') // Set hostname within the network
        ->start();
    $redisHost = $redisContainer->getHost();
    $redisPort = $redisContainer->getPort(6379);

    // Start MariaDB Container
    $mariaDbContainer = GenericContainer::fromImage('mariadb:10.6')
        ->withEnvironment('MARIADB_USER', $mariaDbUser)
        ->withEnvironment('MARIADB_PASSWORD', $mariaDbPassword)
        ->withEnvironment('MARIADB_DATABASE', $mariaDbDatabase)
        ->withEnvironment('MARIADB_ROOT_PASSWORD', 'rootpassword') // Required by image
        ->withExposedPort(3306)
        ->withNetwork($network) // Attach to network
        ->withNetworkAliases('mariadb') // Set hostname within the network
        ->withWait(Wait::forLog('mariadb.org binary distribution')) // Wait for MariaDB to be ready
        ->start();
    $mariaDbHost = $mariaDbContainer->getHost();
    $mariaDbPort = $mariaDbContainer->getPort(3306);

    // Start Mock CyberArk Server Container
    // Mount the test directory and run the mock server script
    $projectRoot = realpath(__DIR__ . '/../');
    $mockCyberarkContainer = GenericContainer::fromImage('php:8.2-cli') // Use a suitable PHP version
        ->withVolume($projectRoot . '/test', '/app/test') // Mount test directory
        ->withWorkingDirectory('/app/test')
        ->withCommand(['php', '-S', '0.0.0.0:' . $mockCyberarkPort, 'mock_cyberark_server.php'])
        ->withExposedPort($mockCyberarkPort)
        ->withNetwork($network) // Attach to network
        ->withNetworkAliases('mockcyberark') // Set hostname within the network
        ->withWait(Wait::forLog('Development Server.*started')) // Wait for PHP dev server
        ->start();
    $mockCyberarkHost = $mockCyberarkContainer->getHost();

    // Update test.ini with dynamic container details
    $iniContent = file_get_contents($testIniPath);
    if ($iniContent === false) {
        throw new \RuntimeException('Failed to read test INI file: ' . $testIniPath);
    }

    // Replace placeholders or existing values using network aliases for hosts
    // Note: Ports are the internal container ports when using network aliases
    $replacements = [
        '/^safeservicewrapper\.base_url\s*=.*/m' => 'safeservicewrapper.base_url = http://mockcyberark:' . $mockCyberarkPort,
        '/^safeservicewrapper\.redis_host\s*=.*/m' => 'safeservicewrapper.redis_host = redis', // Use network alias
        '/^safeservicewrapper\.redis_port\s*=.*/m' => 'safeservicewrapper.redis_port = 6379', // Internal Redis port
        // Add MariaDB settings
        '/^safeservicewrapper\.mariadb_host\s*=.*/m' => 'safeservicewrapper.mariadb_host = mariadb', // Use network alias
        '/^safeservicewrapper\.mariadb_port\s*=.*/m' => 'safeservicewrapper.mariadb_port = 3306', // Internal MariaDB port
        '/^safeservicewrapper\.mariadb_user\s*=.*/m' => 'safeservicewrapper.mariadb_user = testuser',
        '/^safeservicewrapper\.mariadb_password_placeholder\s*=.*/m' => 'safeservicewrapper.mariadb_password_placeholder = ""', // Password fetched via CyberArk, placeholder for now
        '/^safeservicewrapper\.mariadb_database\s*=.*/m' => 'safeservicewrapper.mariadb_database = testdb',
    ];

    $newIniContent = preg_replace(array_keys($replacements), array_values($replacements), $iniContent);

    if ($newIniContent === null) {
        throw new \RuntimeException('Failed to update test INI content using preg_replace.');
    }

    if (file_put_contents($testIniPath, $newIniContent) === false) {
        throw new \RuntimeException('Failed to write updated test INI file: ' . $testIniPath);
    }

    // Store container properties in the test environment
    test()->environment = [
        'network' => $network,
        'redisContainer' => $redisContainer,
        'mariaDbContainer' => $mariaDbContainer,
        'mockCyberarkContainer' => $mockCyberarkContainer,
        'redisHost' => $redisHost,
        'redisPort' => $redisPort,
        'mariaDbHost' => $mariaDbHost,
        'mariaDbPort' => $mariaDbPort,
        'mariaDbUser' => $mariaDbUser,
        'mariaDbPassword' => $mariaDbPassword,
        'mariaDbDatabase' => $mariaDbDatabase,
        'mockCyberarkHost' => $mockCyberarkHost,
        'mockCyberarkPort' => $mockCyberarkPort,
        'testIniPath' => $testIniPath,
    ];
});

afterAll(function () {
    // Extract container properties from the test environment
    $network = test()->environment['network'];
    $redisContainer = test()->environment['redisContainer'];
    $mariaDbContainer = test()->environment['mariaDbContainer'];
    $mockCyberarkContainer = test()->environment['mockCyberarkContainer'];

    // Determine cache path and clear old cache (again, for teardown)
    $testIniPath = __DIR__ . '/test.ini';
    $iniContent = @file_get_contents($testIniPath);
    if ($iniContent !== false) {
        preg_match('/^cache_path\s*=\s*"?([^"]*)"?/m', $iniContent, $matches);
        $cachePath = isset($matches[1]) && !empty($matches[1]) ? $matches[1] : sys_get_temp_dir();
    } else {
        $cachePath = sys_get_temp_dir();
    }
    echo "Clearing cache files from: $cachePath\n";
    $cacheFiles = glob($cachePath . '/cyberark_cache_*');
    if ($cacheFiles) {
        foreach ($cacheFiles as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    $redisContainer?->stop();
    $mariaDbContainer?->stop();
    $mockCyberarkContainer?->stop();
    $network?->remove();
});
