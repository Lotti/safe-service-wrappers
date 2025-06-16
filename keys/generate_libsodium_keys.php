<?php

// Ensure Libsodium is available and initialized
if (!extension_loaded('sodium')) {
  die("Error: The 'sodium' PHP extension is not loaded. Please install it.\n");
}


/**
 * Configuration for key storage.
 * Adjust these paths as needed for your environment.
 */
$key_dir = __DIR__;
$private_key_file = $key_dir . DIRECTORY_SEPARATOR . 'private_key.key';
$public_key_file = $key_dir . DIRECTORY_SEPARATOR . 'public_key.pub';

echo "--- Libsodium Key Generation and Storage ---" . PHP_EOL;

// 1. Create the keys directory if it doesn't exist
if (!is_dir($key_dir)) {
  echo "Creating key directory: {$key_dir}" . PHP_EOL;
  if (!mkdir($key_dir, 0700, true)) { // Permissions 0700: Owner can read, write, execute; others no access
    die("Error: Failed to create key directory with secure permissions.\n");
  }
} else {
  echo "Key directory already exists: {$key_dir}" . PHP_EOL;
}

// 2. Check if keys already exist to prevent accidental overwrites
if (file_exists($private_key_file) || file_exists($public_key_file)) {
  echo "Warning: Key files already exist. Overwriting them." . PHP_EOL;
  echo "If this is not intended, please move or delete the existing files." . PHP_EOL;
  // You could add a user prompt here to confirm overwrite in a real scenario
}

// 3. Generate a new X25519 key pair
echo "Generating new Libsodium key pair..." . PHP_EOL;
$keyPair = sodium_crypto_box_keypair(); // Combined public and private key
$publicKey = sodium_crypto_box_publickey($keyPair);
$privateKey = sodium_crypto_box_secretkey($keyPair);

// 4. Store the private key securely
// Permissions 0600: Owner can read/write; others no access
echo "Storing private key to: {$private_key_file}" . PHP_EOL;
if (file_put_contents($private_key_file, $privateKey) === false) {
  die("Error: Failed to write private key to file.\n");
}
if (!chmod($private_key_file, 0600)) {
  die("Error: Failed to set secure permissions on private key file.\n");
}
echo "Private key stored successfully with permissions 0600." . PHP_EOL;

// 5. Store the public key
// Permissions 0644: Owner read/write; Group read; Others read (more lenient, as it's public)
echo "Storing public key to: {$public_key_file}" . PHP_EOL;
if (file_put_contents($public_key_file, $publicKey) === false) {
  die("Error: Failed to write public key to file.\n");
}
if (!chmod($public_key_file, 0644)) {
  echo "Warning: Failed to set desired permissions on public key file. Please check manually.\n";
}
echo "Public key stored successfully with permissions 0644." . PHP_EOL;

echo "--- Key Generation Complete ---" . PHP_EOL;

// Optional: Display the keys in hex (for verification, NOT for storing this way)
echo "\n--- For Verification (DO NOT STORE IN HEX THIS WAY) ---" . PHP_EOL;
echo "Public Key (Hex): " . sodium_bin2hex($publicKey) . PHP_EOL;
echo "Private Key (Hex): " . sodium_bin2hex($privateKey) . PHP_EOL;
echo "--------------------------------------------------------" . PHP_EOL;

// --- Example of how to load the keys back for use ---
echo "\n--- Loading Keys for Demonstration ---" . PHP_EOL;
try {
  $loadedPrivateKey = file_get_contents($private_key_file);
  $loadedPublicKey = file_get_contents($public_key_file);

  if ($loadedPrivateKey === false || $loadedPublicKey === false) {
    throw new Exception("Failed to load one or both key files.");
  }

  echo "Keys loaded successfully from disk." . PHP_EOL;

  // You can now use $loadedPrivateKey and $loadedPublicKey in your encryption/decryption logic.
  // For example, to verify they match the original generated keys:
  if ($loadedPrivateKey === $privateKey && $loadedPublicKey === $publicKey) {
    echo "Loaded keys match generated keys. All good!" . PHP_EOL;
  } else {
    echo "Loaded keys do NOT match generated keys. There might be an issue." . PHP_EOL;
  }

} catch (Exception $e) {
  echo "Error loading keys: " . $e->getMessage() . PHP_EOL;
}
