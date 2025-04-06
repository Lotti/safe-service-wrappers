#!/bin/bash

set -ex
php zephir.phar fullclean -v
php zephir.phar generate -v
php zephir.phar compile -v
php zephir.phar install -v
./run.sh