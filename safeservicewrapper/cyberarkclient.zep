namespace SafeServiceWrapper;

use SafeServiceWrapper\Cryptography;

/**
 * Client to interact with a CyberArk AIM-like service to fetch passwords using PHP functions.
 * Includes file-based caching, reads configuration from php.ini, and supports PEM/PFX/P12 certs.
 */
class CyberarkClient
{
    // Default configuration values (used if INI settings are missing)
    const CACHE_FILE_PREFIX = "cyberark_cache_";
    /**
     * Fetches a password from the CyberArk AIM service using PHP's cURL functions.
     * Checks cache first, fetches if expired or not found. Reads config from INI.
     *
     * @param string host The target host address (used in the query).
     * @param int port The target host port (used in the query).
     * @param string user The target username (used in the query).
     * @return array ["password": string, "cache_hit": bool]
     * @throws Exception If the request fails or the password cannot be retrieved.
     */
    public static function fetchPassword(string host, int port, string user) -> array
    {
        int timeout, cacheTtl;
        string certType, cacheKey, cacheFilePath, certExtension, cachePath;

        // --- Read Configuration from INI ---
        var appId = ini_get("safeservicewrapper.appid");
        var safe = ini_get("safeservicewrapper.safe");
        var folder = ini_get("safeservicewrapper.folder");
        var baseUrl = ini_get("safeservicewrapper.base_url");
        let timeout = (int)ini_get("safeservicewrapper.timeout");
        var certPath = ini_get("safeservicewrapper.cert_path");
        var certPassword = ini_get("safeservicewrapper.cert_password");
        let cacheTtl = (int)ini_get("safeservicewrapper.cache_ttl");
        let cachePath = ini_get("safeservicewrapper.cache_path") ?: sys_get_temp_dir();

        // --- Cache Check ---
        if cacheTtl > 0 {
            let cacheKey = md5(baseUrl . appId . safe . folder . host . port . user . certPath); // Include certPath in key
            let cacheFilePath = rtrim(cachePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . self::CACHE_FILE_PREFIX . cacheKey;

            if file_exists(cacheFilePath) {
                var encryptedContent, decryptedContent, cachedData;
                let encryptedContent = file_get_contents(cacheFilePath);
                if !empty encryptedContent {
                    let decryptedContent = Cryptography::decryptData(encryptedContent);
                    if decryptedContent !== false {
                        let cachedData = unserialize(decryptedContent);
                        if typeof cachedData == "array" && isset cachedData["expires"] && isset cachedData["password"] && time() < cachedData["expires"] {
                            // Cache hit, decrypted, and valid
                            return [
                                "password": cachedData["password"],
                                "cache_hit": true
                            ];
                        }
                        // Decryption ok, but data invalid/expired - proceed to fetch
                    } else {
                        // Decryption failed (e.g., key change, corruption) - proceed to fetch
                        // Optional: Log decryption error
                        // error_log("CyberarkClient cache decryption failed for: " . cacheFilePath);
                        // Optional: Delete corrupted cache file
                        // unlink(cacheFilePath);
                    }
                } else {
                    // Cache file exists but is empty - proceed to fetch
                }
            }
        }
        // --- End Cache Check ---

        // --- Fetch from Source (CyberArk/Mock) ---
        string url;
        var ch; // Curl handle
        var jsonData;
        array curlOptions;

        // Construct the URL
        let url = baseUrl . "/AIMWebService/api/Accounts?AppID=" . appId . "&Safe=" . safe . "&Folder=" . folder . "&Address=" . host . "&Name=" . user;

        // Initialize cURL
        let ch = curl_init();
        if ch === false {
            throw new \Exception("Failed to initialize cURL session.");
        }

        // Basic cURL options
        let curlOptions = [
            CURLOPT_URL: url,
            CURLOPT_RETURNTRANSFER: true,
            CURLOPT_HEADER: false,
            CURLOPT_HTTPGET: true,
            CURLOPT_TIMEOUT: timeout, // Use timeout from INI
            CURLOPT_USERAGENT: "safe-service-wrapper/".phpversion("safeservicewrapper"),
            CURLOPT_HTTPHEADER: ["Accept: application/json"]
        ];

        // Certificate handling (using certPath/certPassword from INI)
        if !empty certPath {
            let curlOptions[CURLOPT_SSLCERT] = certPath;

            // Determine certificate type from extension
            let certExtension = strtolower(pathinfo(certPath, PATHINFO_EXTENSION));
            if certExtension == "p12" || certExtension == "pfx" {
                let certType = "P12";
            } else {
                // Default to PEM for .pem or unknown extensions
                let certType = "PEM";
            }
            let curlOptions[CURLOPT_SSLCERTTYPE] = certType;

            if !empty certPassword {
                let curlOptions[CURLOPT_SSLCERTPASSWD] = certPassword;
            }
            // Depending on server setup, you might need these for HTTPS:
            // let curlOptions[CURLOPT_SSL_VERIFYPEER] = true; // Enable for production HTTPS
            // let curlOptions[CURLOPT_SSL_VERIFYHOST] = 2;   // Enable for production HTTPS
            // let curlOptions[CURLOPT_CAINFO] = "/path/to/ca/bundle.crt"; // If needed
        } else {
            // For testing against HTTP or local HTTPS with self-signed certs, you might disable verification
             // let curlOptions[CURLOPT_SSL_VERIFYPEER] = false;
             // let curlOptions[CURLOPT_SSL_VERIFYHOST] = false;
        }

        // Set cURL options
        if !curl_setopt_array(ch, curlOptions) {
             curl_close(ch);
             throw new \Exception("Failed to set cURL options.");
        }

        // Execute request
        var responseBody = curl_exec(ch);

        // Check for cURL errors during execution
        if responseBody === false {
            var errorMsg = curl_error(ch);
            curl_close(ch);
            throw new \Exception("cURL request execution failed: " . errorMsg);
        }

        // Get HTTP response code
        var responseCode = curl_getinfo(ch, CURLINFO_RESPONSE_CODE);

        // Close cURL session
        curl_close(ch);

        // Check response code
        if responseCode != 200 {
            throw new \Exception("CyberArk request failed. Code: " . responseCode . ", Body: " . responseBody);
        }

         if empty responseBody {
             throw new \Exception("CyberArk request returned empty body. Code: " . responseCode);
        }

        // Parse JSON
        let jsonData = json_decode(responseBody, true);
        if typeof jsonData != "array" || !isset jsonData["Content"] {
             if json_last_error() != JSON_ERROR_NONE {
                 throw new \Exception("Failed to parse CyberArk JSON response: " . json_last_error_msg() . ". Body: " . responseBody);
             }
            throw new \Exception("Failed to parse CyberArk response or 'Content' key missing. Body: " . responseBody);
        }

        var password = jsonData["Content"];
        if typeof password != "string" || empty password {
             throw new \Exception("Invalid or empty password received from CyberArk. Body: " . responseBody);
        }
        // --- End Fetch ---

        // --- Store in Cache ---
        if cacheTtl > 0 && cacheFilePath { // Only cache if TTL > 0 and path was determined
             var cacheData = [
                 "expires": time() + cacheTtl,
                 "password": password
             ];
             var serializedData, encryptedData;
             let serializedData = serialize(cacheData);
             let encryptedData = Cryptography::encryptData(serializedData);

             if encryptedData !== false {
                 // Ensure cache directory exists
                 if !is_dir(cachePath) {
                     // Attempt to create recursively
                     mkdir(cachePath, 0777, true);
                 }
                 // Write encrypted data to cache file only if directory exists or was created
                 if is_dir(cachePath) && is_writable(cachePath) {
                    file_put_contents(cacheFilePath, encryptedData);
                    // Consider adding file locking (flock) for concurrent requests if necessary
                 } else {
                    // Optional: Log a warning if cache directory is not writable
                    // error_log("CyberarkClient cache directory not writable: " . cachePath);
                 }
             } else {
                 // Optional: Log encryption error
                 // error_log("CyberarkClient cache encryption failed.");
             }
        }
        // --- End Store ---

        // Return fetched password and cache status
        return [
            "password": password,
            "cache_hit": false // It was fetched, not from cache this time
        ];
    }
}
