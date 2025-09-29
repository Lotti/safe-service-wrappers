#!/bin/bash

set -ex
export CFLAGS="$CFLAGS -fpermissive"
php zephir-lts.phar fullclean -v
php zephir-lts.phar generate -v
php zephir-lts.phar compile -v
php zephir-lts.phar install -v
