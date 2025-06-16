namespace SafeServiceWrapper;

/**
 * Provides hybrid encryption and decryption functionalities using Libsodium.
 * Data is encrypted with ChaCha20-Poly1305 (AEAD).
 * The symmetric key is encrypted using Curve25519 (crypto_box_seal).
 * Key paths are read from php.ini.
 */
class Cryptography
{
    // --- Configuration ---
    // INI setting names for key paths
    const INI_PUBLIC_KEY_PATH = "safeservicewrapper.crypto.public_key_path";
    const INI_PRIVATE_KEY_PATH = "safeservicewrapper.crypto.private_key_path";

    // Libsodium constants (adjust if needed, but these are standard)
    const SYMMETRIC_KEY_BYTES = SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_KEYBYTES;
    const NONCE_BYTES = SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES; // Nonce public bytes

    // --- Helper Methods ---

    /**
     * Loads the raw public key bytes from the path specified in INI settings.
     * @return string|boolean The raw public key bytes or false on failure.
     */
    private static function loadPublicKeyBytes() -> string | boolean
    {
        var publicKeyPath;
        let publicKeyPath = ini_get(self::INI_PUBLIC_KEY_PATH);
        if empty publicKeyPath || !file_exists(publicKeyPath) || !is_readable(publicKeyPath) {
            // error_log("Libsodium Cryptography Error: Public key path invalid or not readable: " . publicKeyPath);
            return false;
        }

        var publicKeyBytes = file_get_contents(publicKeyPath);
        if publicKeyBytes === false || strlen(publicKeyBytes) != SODIUM_CRYPTO_BOX_PUBLICKEYBYTES {
             // error_log("Libsodium Cryptography Error: Failed to read public key file or invalid length: " . publicKeyPath);
             return false;
        }
        return publicKeyBytes;
    }

     /**
     * Loads the raw private key bytes from the path specified in INI settings.
     * IMPORTANT: Ensure the private key file has strict permissions (readable only by web server user).
     * @return string|boolean The raw private key bytes or false on failure.
     */
    private static function loadPrivateKeyBytes() -> string | boolean
    {
        var privateKeyPath;
        let privateKeyPath = ini_get(self::INI_PRIVATE_KEY_PATH);
         if empty privateKeyPath || !file_exists(privateKeyPath) || !is_readable(privateKeyPath) {
            // error_log("Libsodium Cryptography Error: Private key path invalid or not readable: " . privateKeyPath);
            return false;
        }

        var privateKeyBytes = file_get_contents(privateKeyPath);
        if privateKeyBytes === false || strlen(privateKeyBytes) != SODIUM_CRYPTO_BOX_SECRETKEYBYTES {
             // error_log("Libsodium Cryptography Error: Failed to read private key file or invalid length: " . privateKeyPath);
             return false;
        }
        return privateKeyBytes;
    }

    // --- Core Hybrid Encryption/Decryption ---

    /**
     * Encrypts data using Libsodium Hybrid Encryption.
     *
     * @param string data The plaintext data to encrypt.
     * @return string|boolean The base64 encoded encrypted payload (structure: [encrypted_sym_key][nonce][aead_ciphertext]) or false on failure.
     */
    public static function encryptData(string data) -> string | boolean
    {
        var error;

        // 1. Load Recipient's Public Key bytes
        var publicKeyBytes = self::loadPublicKeyBytes();
        if publicKeyBytes === false {
            return false; // Error logged in helper
        }

        // 2. Generate random symmetric key (for AEAD)
        var symmetricKey = random_bytes(self::SYMMETRIC_KEY_BYTES);

        // 3. Generate nonce for AEAD
        var nonce = random_bytes(self::NONCE_BYTES);

        // 4. Encrypt the symmetric key using crypto_box_seal (anonymous public-key encryption)
        var encryptedSymmetricKey;
        try {
            let encryptedSymmetricKey = sodium_crypto_box_seal(symmetricKey, publicKeyBytes);
        } catch \SodiumException| \TypeError| \Exception, error {
            error_log("Libsodium Cryptography Error: sodium_crypto_box_seal failed. ". error->getMessage());
            return false;
        }
        if empty encryptedSymmetricKey {
             // error_log("Libsodium Cryptography Error: sodium_crypto_box_seal returned empty.");
             return false;
        }


        // 5. Encrypt data with AEAD (ChaCha20-Poly1305) using the symmetric key and nonce
        var encryptedData;
        try {
             // Additional authenticated data (AAD) can be added here if needed (e.g., cache key)
             // let encryptedData = sodium_crypto_aead_chacha20poly1305_encrypt(data, "", nonce, symmetricKey);
             let encryptedData = sodium_crypto_aead_chacha20poly1305_encrypt(data, "", nonce, symmetricKey);
        } catch \SodiumException| \TypeError| \Exception, error {
            error_log("Libsodium Cryptography Error: AEAD encryption failed. ". error->getMessage());
            return false;
        }
         if empty encryptedData {
             // error_log("Libsodium Cryptography Error: AEAD encryption returned empty.");
             return false;
        }

        // 6. Combine parts: [encrypted_sym_key][nonce][aead_ciphertext]
        // Prepend length of encrypted_sym_key (as 2-byte unsigned short) to allow parsing on decrypt
        var encryptedKeyLengthBytes = pack("n", strlen(encryptedSymmetricKey)); // "n" = unsigned short, big endian

        string payload = encryptedKeyLengthBytes . encryptedSymmetricKey . nonce . encryptedData;

        // 7. Base64 encode for safe storage/transmission
        return base64_encode(payload);
    }

    /**
     * Decrypts data encrypted with the Libsodium hybrid scheme.
     *
     * @param string encryptedPayload The base64 encoded encrypted payload.
     * @return string|boolean The original plaintext data or false on failure.
     */
    public static function decryptData(string encryptedPayload) -> string | boolean
    {
        var error;

        // 1. Base64 Decode
        var decodedPayload = base64_decode(encryptedPayload, true);
        if decodedPayload === false {
            // error_log("Libsodium Cryptography Error: Base64 decode failed.");
            return false;
        }

        // 2. Extract length of encrypted symmetric key (first 2 bytes)
        if strlen(decodedPayload) < 2 {
             // error_log("Libsodium Cryptography Error: Payload too short for key length.");
             return false;
        }
        var encryptedKeyLengthData = unpack("nlen", substr(decodedPayload, 0, 2));
        if encryptedKeyLengthData === false || !isset encryptedKeyLengthData["len"] {
             // error_log("Libsodium Cryptography Error: Could not unpack key length.");
             return false;
        }
        int encryptedKeyLength = (int) encryptedKeyLengthData["len"];

        // 3. Extract encrypted symmetric key
        if strlen(decodedPayload) < (2 + encryptedKeyLength) {
             // error_log("Libsodium Cryptography Error: Payload too short for encrypted key.");
             return false;
        }
        var encryptedSymmetricKey = substr(decodedPayload, 2, encryptedKeyLength);

        // 4. Extract Nonce
         if strlen(decodedPayload) < (2 + encryptedKeyLength + self::NONCE_BYTES) {
             // error_log("Libsodium Cryptography Error: Payload too short for nonce.");
             return false;
        }
        var nonce = substr(decodedPayload, 2 + encryptedKeyLength, self::NONCE_BYTES);

        // 5. Extract AEAD Ciphertext
        var ciphertext = substr(decodedPayload, 2 + encryptedKeyLength + self::NONCE_BYTES);
        if empty ciphertext {
            // error_log("Libsodium Cryptography Error: Ciphertext is empty.");
            return false;
        }

        // 6. Load own Public and Private Key bytes to form a keypair for crypto_box_seal_open
        var publicKeyBytes = self::loadPublicKeyBytes();
        var privateKeyBytes = self::loadPrivateKeyBytes();
        if publicKeyBytes === false || privateKeyBytes === false {
            return false; // Error logged in helpers
        }
        var keyPair = sodium_crypto_box_keypair_from_secretkey_and_publickey(privateKeyBytes, publicKeyBytes);
         if keyPair === false {
             // error_log("Libsodium Cryptography Error: Failed to create keypair from loaded keys.");
             return false;
         }

        // 7. Decrypt the symmetric key using crypto_box_seal_open
        var decryptedSymmetricKey;
        try {
            let decryptedSymmetricKey = sodium_crypto_box_seal_open(encryptedSymmetricKey, keyPair);
        } catch \SodiumException| \TypeError| \Exception, error {
            error_log("Libsodium Cryptography Error: sodium_crypto_box_seal_open failed (wrong key? corrupted data?). ".error->getMessage());
            return false; // Decryption failed (wrong key, corrupted data, etc.)
        }
         if decryptedSymmetricKey === false || strlen(decryptedSymmetricKey) != self::SYMMETRIC_KEY_BYTES {
             // error_log("Libsodium Cryptography Error: Symmetric key decryption failed or key length mismatch.");
             return false;
         }

        // 8. Decrypt the actual data using the decrypted symmetric key and nonce
        var decryptedData;
        try {
             // Ensure AAD matches encryption if it was used (here it was "")
             // let decryptedData = sodium_crypto_aead_chacha20poly1305_decrypt(ciphertext, "", nonce, decryptedSymmetricKey);
             let decryptedData = sodium_crypto_aead_chacha20poly1305_decrypt(ciphertext, "", nonce, decryptedSymmetricKey);
        } catch \SodiumException| \TypeError| \Exception, error {
            error_log("Libsodium Cryptography Error: AEAD decryption failed (tampered data? wrong key?). ".error->getMessage());
            return false; // Decryption failed (tampered data, wrong key, etc.)
        }
         if decryptedData === false {
             // error_log("Libsodium Cryptography Error: AEAD decryption returned false.");
             return false;
         }

        // 9. Return original data
        return decryptedData;
    }
}
