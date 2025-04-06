#!/bin/bash

# Function to clean up the server process
cleanup() {
  echo "Stopping mock server..."
  if [ -f server.pid ]; then
    kill $(cat server.pid) &> /dev/null # Kill process, suppress output
    rm server.pid
    echo "Mock server stopped."
  else
    echo "Mock server PID file not found."
  fi
}

# Trap signals to ensure cleanup runs
trap cleanup EXIT INT TERM

echo "Determining cache path and clearing old cache..."
# Get cache path from PHP (reads INI or defaults to system temp)
# Note: This assumes 'php' executable is in PATH and can read relevant ini files
CACHE_PATH=$(php -r 'echo ini_get("safeservicewrapper.cache_path") ?: sys_get_temp_dir();')
if [ -z "$CACHE_PATH" ]; then
  echo "Warning: Could not determine cache path. Skipping cache clear."
else
  # Remove previous cache files, suppressing errors if none exist
  echo "Clearing cache files from: $CACHE_PATH"
  rm -f "${CACHE_PATH}/cyberark_cache_"*
fi

echo "Starting mock server..."
# Start PHP built-in server in the background on port 8080
# Redirect stdout and stderr to /dev/null
# Save the background process ID (PID) to server.pid
php -S localhost:8080 mock_cyberark_server.php > /dev/null 2>&1 & echo $! > server.pid

# Check if server started (optional, basic check)
if [ $? -ne 0 ] || ! [ -f server.pid ]; then
    echo "Failed to start mock server."
    exit 1
fi

echo "Mock server started with PID $(cat server.pid). Waiting a moment..."
sleep 1 # Give the server a second to start up

# Run the original test script
# Note: The USER/PASSWORD env vars might not be needed if test.php now uses the Cyberark client
echo "Running test script..."
USER=test PASSWORD=1234 php test.php

# Cleanup will be triggered by the trap on exit
echo "Test script finished."
