<?php

namespace Test\unit\maria;

use Test\GlobalTest;

final class MariaDbPdoTest extends GlobalTest
{
  public function testMariaDbPDOWrapper(): void
  {
    $host = getnev('MARIADB_HOST');
    $port = getnev('MARIADB_PORT');
    $user = getnev('MARIADB_USER');
    $dbname = getnev('MARIADB_DATABASE');

    // Create a MariaDbPdo instance
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname";
    $pdo = new \SafeServiceWrapper\Pdo($dsn, $user, "");
    $this->assertInstanceOf('PDO', $pdo, "Constructor should return instance of PDO class");

    // Perform a simple query
    $result = $pdo->query("SELECT 1 + 1");
    $this->assertInstanceOf('PDOStatement', $result, "Querying should return instance of PDOStatement class");

    $row = $result->fetch(\PDO::FETCH_NUM);
    $this->assertSame($row, [2], "Row should be equal to [2]");

    $pdo->close();
  }
}
