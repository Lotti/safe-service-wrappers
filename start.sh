#!/bin/bash

set -e

# Set container names
VALKEY_CONTAINER_NAME="${1:-test_valkey}"
MARIADB_CONTAINER_NAME="${2:-test_mariadb}"
CYBERARK_CONTAINER_NAME="${3:-test_cyberark}"
NETWORK_NAME="${4:-test_network}"

# Create network
docker network create --driver bridge "${NETWORK_NAME}" || true

# Run Redis container
VALKEY_CONTAINER_ID=$(docker run -d --rm \
  --name "${VALKEY_CONTAINER_NAME}" \
  --network "${NETWORK_NAME}" \
  -p 6379:6379 \
  -v ./tests/valkey_conf:/usr/local/etc/valkey \
  valkey/valkey:8.1-alpine \
  valkey-server /usr/local/etc/valkey/valkey.conf)
echo "Valkey container started: ${VALKEY_CONTAINER_ID}"

# Run MariaDB container
MARIADB_CONTAINER_ID=$(docker run -d --rm --name "${MARIADB_CONTAINER_NAME}" \
  --network "${NETWORK_NAME}" \
  -e MARIADB_ROOT_PASSWORD=rootpassword \
  -e MARIADB_USER=testuser \
  -e MARIADB_PASSWORD=testpassword \
  -e MARIADB_DATABASE=testdb \
  -p 3307:3306 \
  mariadb:10.6)
echo "MariaDB container started: ${MARIADB_CONTAINER_ID}"

# Run Mock CyberArk container
PROJECT_ROOT=$(pwd)
CYBERARK_CONTAINER_ID=$(docker run -d --rm --name "${CYBERARK_CONTAINER_NAME}" \
  --network "${NETWORK_NAME}" \
  -v "${PROJECT_ROOT}/tests/mocks:/app" \
  -p 3000:8080 \
  php:8.3-cli \
  php -S 0.0.0.0:8080 -t /app /app/mock_cyberark_server.php)
echo "CyberArk container started: ${CYBERARK_CONTAINER_ID}"

set +e