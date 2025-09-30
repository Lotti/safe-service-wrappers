#!/bin/bash

set -ex
export CFLAGS="$CFLAGS -fpermissive"
php zephir.phar fullclean -v
php zephir.phar generate -v
php zephir.phar compile -v
php zephir.phar install -v