<?php

namespace Test;

use PHPUnit\Framework\TestCase;

abstract class GlobalTest extends TestCase {
  public static function setUpBeforeClass(): void {
    echo "Starting containers...\n";

    $valkeyContainerName = getenv('VALKEY_CONTAINER');
    $mariaDBContainerName = getenv('MARIADB_CONTAINER');
    $cyberarkMockContainerName = getenv('CYBERARKMOCK_CONTAINER');
    $networkName = getenv('CONTAINER_NETWORK');

    exec("./start.sh $valkeyContainerName $mariaDBContainerName $cyberarkMockContainerName $networkName 2>&1");

    # Wait for containers to start (crude check)
    sleep(5);
  }

  public static function tearDownAfterClass(): void {
    echo "Stopping containers...\n";

    $valkeyContainerName = getenv('VALKEY_CONTAINER');
    $mariaDBContainerName = getenv('MARIADB_CONTAINER');
    $cyberarkMockContainerName = getenv('CYBERARKMOCK_CONTAINER');
    $networkName = getenv('CONTAINER_NETWORK');

    exec("./stop.sh $valkeyContainerName $mariaDBContainerName $cyberarkMockContainerName $networkName 2>&1");
  }
}