<?php

declare(strict_types=1);

namespace SafeServiceWrapper\Tests;

use Docker\Docker;
use PHPUnit\Framework\TestCase;
use Testcontainers\Container;
use Testcontainers\GenericContainer;
use Testcontainers\Network; // Import Network
use Testcontainers\Wait;

/**
 * Base class for integration tests requiring Docker containers.
 * Manages Redis, MariaDB, and Mock CyberArk Server containers.
 */
abstract class IntegrationTestCase extends TestCase
{
    protected static ?Network $network = null; // Shared network
    protected static ?Container $redisContainer = null;
    protected static ?Container $mariaDbContainer = null;
    protected static ?Container $mockCyberarkContainer = null;

    protected static string $redisHost;
    protected static int $redisPort;
    protected static string $mariaDbHost;
    protected static int $mariaDbPort;
    protected static string $mariaDbUser = 'testuser';
    protected static string $mariaDbPassword = 'testpassword';
    protected static string $mariaDbDatabase = 'testdb';
    protected static string $mockCyberarkHost;
    protected static int $mockCyberarkPort = 8080; // Default port for the mock server script

    // Path to the test INI file relative to the project root
    protected const TEST_INI_PATH = __DIR__ . '/../test/test.ini';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Create a shared network for containers to communicate by hostname
        self::$network = Network::create();

        // Start Redis Container
        self::$redisContainer = GenericContainer::fromImage('redis:alpine')
            ->withExposedPort(6379)
            ->withNetwork(self::$network) // Attach to network
            ->withNetworkAliases('redis') // Set hostname within the network
            ->start();
        self::$redisHost = self::$redisContainer->getHost();
        self::$redisPort = self::$redisContainer->getPort(6379);

        // Start MariaDB Container
        self::$mariaDbContainer = GenericContainer::fromImage('mariadb:10.6')
            ->withEnvironment('MARIADB_USER', self::$mariaDbUser)
            ->withEnvironment('MARIADB_PASSWORD', self::$mariaDbPassword)
            ->withEnvironment('MARIADB_DATABASE', self::$mariaDbDatabase)
            ->withEnvironment('MARIADB_ROOT_PASSWORD', 'rootpassword') // Required by image
            ->withExposedPort(3306)
            ->withNetwork(self::$network) // Attach to network
            ->withNetworkAliases('mariadb') // Set hostname within the network
            ->withWait(Wait::forLog('mariadb.org binary distribution')) // Wait for MariaDB to be ready
            ->start();
        self::$mariaDbHost = self::$mariaDbContainer->getHost();
        self::$mariaDbPort = self::$mariaDbContainer->getPort(3306);

        // Start Mock CyberArk Server Container
        // Mount the test directory and run the mock server script
        $projectRoot = realpath(__DIR__ . '/..');
        self::$mockCyberarkContainer = GenericContainer::fromImage('php:8.2-cli') // Use a suitable PHP version
            ->withVolume($projectRoot . '/test', '/app/test') // Mount test directory
            ->withWorkingDirectory('/app/test')
            ->withCommand(['php', '-S', '0.0.0.0:' . self::$mockCyberarkPort, 'mock_cyberark_server.php'])
            ->withExposedPort(self::$mockCyberarkPort)
            ->withNetwork(self::$network) // Attach to network
            ->withNetworkAliases('mockcyberark') // Set hostname within the network
            ->withWait(Wait::forLog('Development Server.*started')) // Wait for PHP dev server
            ->start();
        self::$mockCyberarkHost = self::$mockCyberarkContainer->getHost();
        // Port is fixed inside the container, but we use the mapped host port for external access if needed
        // However, for internal communication via network alias, use the fixed port
        // self::$mockCyberarkPort = self::$mockCyberarkContainer->getPort(self::$mockCyberarkPort); // Use mapped port if accessing from host

        // Update test.ini with dynamic container details
        self::updateTestIni();
    }

    public static function tearDownAfterClass(): void
    {
        self::$redisContainer?->stop();
        self::$mariaDbContainer?->stop();
        self::$mockCyberarkContainer?->stop();
        self::$network?->remove(); // Remove the network

        // Optional: Restore original test.ini if needed, but usually not necessary for tests
        // self::restoreTestIni(); // Implement if you have a backup mechanism

        parent::tearDownAfterClass();
    }

    /**
     * Updates the test/test.ini file with dynamic container connection details.
     * Uses network aliases for inter-container communication.
     */
    protected static function updateTestIni(): void
    {
        $iniContent = file_get_contents(self::TEST_INI_PATH);
        if ($iniContent === false) {
            throw new \RuntimeException('Failed to read test INI file: ' . self::TEST_INI_PATH);
        }

        // Replace placeholders or existing values using network aliases for hosts
        // Note: Ports are the internal container ports when using network aliases
        $replacements = [
            '/^safeservicewrapper\.base_url\s*=.*/m' => 'safeservicewrapper.base_url = http://mockcyberark:' . self::$mockCyberarkPort,
            '/^safeservicewrapper\.redis_host\s*=.*/m' => 'safeservicewrapper.redis_host = redis', // Use network alias
            '/^safeservicewrapper\.redis_port\s*=.*/m' => 'safeservicewrapper.redis_port = 6379', // Internal Redis port
            // Add MariaDB settings
            '/^safeservicewrapper\.mariadb_host\s*=.*/m' => 'safeservicewrapper.mariadb_host = mariadb', // Use network alias
            '/^safeservicewrapper\.mariadb_port\s*=.*/m' => 'safeservicewrapper.mariadb_port = 3306', // Internal MariaDB port
            '/^safeservicewrapper\.mariadb_user\s*=.*/m' => 'safeservicewrapper.mariadb_user = ' . self::$mariaDbUser,
            '/^safeservicewrapper\.mariadb_password_placeholder\s*=.*/m' => 'safeservicewrapper.mariadb_password_placeholder = ""', // Password fetched via CyberArk, placeholder for now
            '/^safeservicewrapper\.mariadb_database\s*=.*/m' => 'safeservicewrapper.mariadb_database = ' . self::$mariaDbDatabase,
        ];

        $newIniContent = preg_replace(array_keys($replacements), array_values($replacements), $iniContent);

        if ($newIniContent === null) {
            throw new \RuntimeException('Failed to update test INI content using preg_replace.');
        }

        if (file_put_contents(self::TEST_INI_PATH, $newIniContent) === false) {
            throw new \RuntimeException('Failed to write updated test INI file: ' . self::TEST_INI_PATH);
        }

        // Give services a moment to fully initialize after config change (optional)
        // sleep(1);
    }

    // Optional: Add a method to restore the original INI if needed
    // protected static function restoreTestIni(): void { ... }
}
