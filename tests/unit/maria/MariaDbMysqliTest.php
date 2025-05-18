<?php

namespace Test\unit\maria;

use Test\GlobalTest;

final class MariaDbMysqliTest extends GlobalTest
{
  public function testMysqliWrapper(): void
  {
    // Use the network alias and internal port configured in test.ini
    $host = ini_get('safeservicewrapper.mariadb_host');
    $port = ini_get('safeservicewrapper.mariadb_port');
    $user = ini_get('safeservicewrapper.mariadb_user');
    $dbname = ini_get('safeservicewrapper.mariadb_database');

    // Create a MariaDbMysqli instance
    $mysqli = new \SafeServiceWrapper\Mysqli($host, $user, $dbname, $port);

    $this->assertInstanceOf('mysqli', $mysqli, "Constructor should return instance of mysqli class");

    // Perform a simple query
    $result = $mysqli->query("SELECT 1 + 1");
    $this->assertInstanceOf('mysqli_result', $result, "Querying should return instance of mysqli_result class");

    $row = $result->fetch_row();
    $this->assertSame($row, [2], "Row should be equal to [2]");

    $mysqli->close();
  }
}
