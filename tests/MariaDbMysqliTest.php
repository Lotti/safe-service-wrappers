<?php

use SafeServiceWrapper\MariaDbMysqli;

uses()->group('mariadb');

it('connects to MariaDB using MariaDbMysqli and performs a query', function () {
    // Use the network alias and internal port configured by IntegrationTestCase
    $host = test()->environment['mariaDbHost'];
    $port = test()->environment['mariaDbPort'];
    $user = test()->environment['mariaDbUser'];
    $dbname = test()->environment['mariaDbDatabase'];

    // Create a MariaDbMysqli instance
    $mysqli = new MariaDbMysqli($host, $user, $dbname, $port);

    expect($mysqli)->toBeInstanceOf(MariaDbMysqli::class);

    // Perform a simple query
    $result = $mysqli->query("SELECT 1 + 1");
    expect($result)->toBeInstanceOf(\mysqli_result::class);

    $row = $result->fetch_row();
    expect($row)->toBe(["2"]);

    $mysqli->close();
});
