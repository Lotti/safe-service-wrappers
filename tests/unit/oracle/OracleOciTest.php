<?php

namespace Test\unit\oracle;

use Test\GlobalTest;

final class OracleOciTest extends GlobalTest
{
  public function testOracleOciWrapper(): void
  {
    // Use the network alias and internal port configured in test.ini
    $host = getenv('ORACLE_HOST');
    $port = getenv('ORACLE_PORT');
    $user = getenv('ORACLE_USER');
    $dbname = getenv('ORACLE_DATABASE');

  }
}
