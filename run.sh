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

  echo "Stopping Redis server..."
  if [ -f redis.pid ]; then
    kill $(cat redis.pid) &> /dev/null
    rm redis.pid
    echo "Redis server stopped."
  else
    echo "Redis server PID file not found."
  fi
}

# Trap signals to ensure cleanup runs
trap cleanup EXIT INT TERM

echo "Starting Redis server..."
# Start Redis server using valkey.sh in the background
./valkey.sh > /dev/null 2>&1 & echo $! > redis.pid

# Check if server started (optional, basic check)
if [ $? -ne 0 ] || ! [ -f redis.pid ]; then
    echo "Failed to start Redis server."
    exit 1
fi
echo "Redis server started with PID $(cat redis.pid). Waiting a moment..."
sleep 1 # Give redis a second to start up

echo "Determining cache path and clearing old cache..."
# Parse cache_path from test.ini or use system temp dir
if [ -f "test/test.ini" ]; then
  # Extract cache_path from test.ini using grep and sed
  # Look for "cache_path = " in the file, extract the value, and remove quotes
  CACHE_PATH=$(grep -E "^cache_path\s*=" test/test.ini | sed -E 's/^cache_path\s*=\s*"?([^"]*)"?.*/\1/')
  
  # If cache_path is empty or not found, use system temp dir
  if [ -z "$CACHE_PATH" ]; then
    CACHE_PATH=$(php -r 'echo sys_get_temp_dir();')
    echo "No cache_path found in test.ini, using system temp: $CACHE_PATH"
  else
    echo "Found cache_path in test.ini: $CACHE_PATH"
  fi
else
  # If test.ini doesn't exist, use system temp dir
  CACHE_PATH=$(php -r 'echo sys_get_temp_dir();')
  echo "test.ini not found, using system temp: $CACHE_PATH"
fi

# Remove previous cache files, suppressing errors if none exist
echo "Clearing cache files from: $CACHE_PATH"
rm -f "${CACHE_PATH}/cyberark_cache_"*

echo "Starting mock server..."
# Start PHP built-in server in the background on port 8080
# Redirect stdout and stderr to /dev/null
# Save the background process ID (PID) to server.pid
php -S localhost:8080 test/mock_cyberark_server.php > /dev/null 2>&1 & echo $! > server.pid

# Check if server started (optional, basic check)
if [ $? -ne 0 ] || ! [ -f server.pid ]; then
    echo "Failed to start mock server."
    exit 1
fi

echo "Mock server started with PID $(cat server.pid). Waiting a moment..."
sleep 1 # Give the server a second to start up

# Run the test script
echo "Running test script..."
php test/test.php

# Cleanup will be triggered by the trap on exit
echo "Test script finished."
