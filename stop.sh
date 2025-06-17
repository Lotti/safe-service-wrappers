#!/bin/bash

set -e

# Set container names
VALKEY_CONTAINER_NAME="${1:-test_valkey}"
MARIADB_CONTAINER_NAME="${2:-test_mariadb}"
ORACLE_CONTAINER_NAME="${3:-test_oracle}"
MONGODB_CONTAINER_NAME="${4:-test_oracle}"
CYBERARK_CONTAINER_NAME="${5:-test_cyberark}"
NETWORK_NAME="${6:-test_network}"

docker stop "${VALKEY_CONTAINER_NAME}" "${MARIADB_CONTAINER_NAME}" "${ORACLE_CONTAINER_NAME}" "${MONGODB_CONTAINER_NAME}" "${CYBERARK_CONTAINER_NAME}"

docker network rm "${NETWORK_NAME}" || true

set +e
