<?php

namespace Test\unit\oracle;

use Test\GlobalTest;

final class MongoTest extends GlobalTest
{
  public function testMongoWrapper(): void
  {
    // Use the network alias and internal port configured in test.ini
    $host = getenv('MONGO_HOST');
    $port = getenv('MONGO_PORT');
    $user = getenv('MONGO_USER');
    $dbname = getenv('MONGO_DATABASE');

  }
}
