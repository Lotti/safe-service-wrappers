<?php

use SafeServiceWrapper\MariaDbPdo;

uses()->group('mariadb');

it('connects to MariaDB using MariaDbPdo and performs a query', function () {
    // Use the network alias and internal port configured by IntegrationTestCase
    $host = test()->environment['mariaDbHost'];
    $port = test()->environment['mariaDbPort'];
    $user = test()->environment['mariaDbUser'];
    $dbname = test()->environment['mariaDbDatabase'];

    // Create a MariaDbPdo instance
    $pdo = new MariaDbPdo($host, $port, $user, $dbname);

    expect($pdo)->toBeInstanceOf(MariaDbPdo::class);

    // Perform a simple query
    $result = $pdo->query("SELECT 1 + 1");
    expect($result)->toBeInstanceOf(\PDOStatement::class);

    $row = $result->fetch(\PDO::FETCH_NUM);
    expect($row)->toBe([2]);
});
