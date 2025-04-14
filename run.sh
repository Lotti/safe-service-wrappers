#!/bin/bash

#!/bin/bash

# This script now runs PEST tests.
# Testcontainers within PEST manages Redis, MariaDB, and the mock server.

# Ensure vendor directory exists
if [ ! -d "vendor" ]; then
  echo "Vendor directory not found. Running composer install..."
  composer install --no-interaction --no-progress
  if [ $? -ne 0 ]; then
    echo "Composer install failed."
    exit 1
  fi
fi

# Run PEST tests
echo "Running PEST tests..."
vendor/bin/pest

# Store the exit code of PEST
PEST_EXIT_CODE=$?

echo "PEST finished with exit code $PEST_EXIT_CODE."

# Exit with the PEST exit code
exit $PEST_EXIT_CODE
