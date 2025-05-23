namespace SafeServiceWrapper;

class Redis extends \Redis {
  /**
   * Gets credentials for Redis authentication.
   * If credentials is an array, it's assumed to be [username, null] and we fetch the password.
   * If credentials is not an array, we fetch the password directly.
   *
   * @param mixed credentials The credentials passed to auth() or constructor
   * @return mixed The processed credentials with password from CyberArk
   */
  private function getCredentials(var host, var port, var credentials) -> array|string {
    var password, cyberarkResult, user = "", cyberarkException;
    
    // Determine user from credentials or default to empty string
    if is_array(credentials) && isset credentials[0] {
      let user = credentials[0];
    }

    try {
      // Call CyberarkClient to fetch password
      let cyberarkResult = \SafeServiceWrapper\CyberarkClient::fetchPassword(host, port, user);
      
      // Extract password from result (CyberarkClient returns ["password": string, "cache_hit": bool])
      if typeof cyberarkResult == "array" && isset cyberarkResult["password"] {
        let password = cyberarkResult["password"];
        
        // Optional: Log cache hit/miss status for debugging
        if isset cyberarkResult["cache_hit"] && cyberarkResult["cache_hit"] {
          echo "CyberArk password cache hit for " . host . ":" . port. "@" . user . "\n";
        } else {
          echo "CyberArk password cache miss for " . host . ":" . port. "@" . user . "\n";
        }
      }
    } catch \Exception, cyberarkException {
      throw new \Exception("Error while retrieving Redis password from Cyberark: " . cyberarkException->getMessage());      // Fallback to environment variable if CyberarkClient throws an exception
    }
    
    // Return credentials in the expected format
    if is_array(credentials) {
      return [credentials[0], password];
    } else {
      return password;
    }
  }

  public function __construct(array options = []) {
    var_export(options, true);

    if isset options["host"] {
      if !isset options["port"] {
        let options["port"] = 6379;
      }

      if isset options["auth"] {
        let options["auth"] = this->getCredentials(options["host"], options["port"], options["auth"]);  
      } else {
        let options["auth"] = this->getCredentials(options["host"], options["port"], null);
      }
    }
    parent::__construct(options);
  }

  public function auth(var credentials) -> boolean {
    return parent::auth(this->getCredentials(this->getHost(), this->getPort(), credentials));
  }

  public function getAuth() -> mixed {
    return null;
  }

  /**
   * Configures PHP session handling to use Redis based on INI settings.
   * This method should be called from PHP userland *before* session_start().
   * Reads settings like:
   * - safeservicewrapper.session_host
   * - safeservicewrapper.session_port
   * - safeservicewrapper.session_user
   * - safeservicewrapper.session_prefix
   * - safeservicewrapper.session_timeout
   * - safeservicewrapper.session_database
   * - safeservicewrapper.session_persistent
   * - safeservicewrapper.session_lock_retries
   * - safeservicewrapper.session_lock_wait_time
   */
  public static function configureSessionHandling() -> void
  {
      string host, user, prefix, savePath;
      int port, weight, timeout, database, persistent;
      array parts;

      // Read settings from INI, providing defaults
      let host = ini_get("safeservicewrapper.session_host") ?: "127.0.0.1";
      let port = (int)(ini_get("safeservicewrapper.session_port") ?: 6379);
      let user = ini_get("safeservicewrapper.session_user") ?: ""; // Empty means no auth
      let prefix = ini_get("safeservicewrapper.session_prefix") ?: "PHPREDIS_SESSION:";
      let weight = (int)(ini_get("safeservicewrapper.weight") ?: 1); // 0 means use default php.ini session.gc_maxlifetime
      let timeout = (int)(ini_get("safeservicewrapper.session_timeout") ?: 0); // 0 means use default php.ini session.gc_maxlifetime
      let database = (int)(ini_get("safeservicewrapper.session_database") ?: 0);
      let persistent = (int)(ini_get("safeservicewrapper.session_persistent") ?: 0); // 0 = non-persistent

      // Construct the save_path string
      // Format: tcp://host:port?auth=password&prefix=prefix&timeout=timeout&database=db&persistent=1&retry_interval=10&read_timeout=2
      // Note: phpredis uses 'auth' not 'password' in the query string
      let savePath = "tcp://" . host . ":" . port;
      let parts = [];

      let parts[] = "weight=" . weight;
      if timeout > 0 {
          let parts[] = "timeout=" . timeout;
      }
      let parts[] = "persistent=".persistent;
      if !empty prefix {
          let parts[] = "prefix=" . urlencode(prefix);
      }
      if !empty user {
          let parts[] = "auth[]=" . urlencode(user); // URL-encode password just in case
          // TODO: recover password from cyberark
          // let parts[] = "auth[]=" . urlencode(password); // URL-encode password just in case
      }
      let parts[] = "database=" . database;

      if count(parts) > 0 {
          let savePath .= "?" . implode("&", parts);
      }

      // Set the INI settings
      ini_set("session.save_handler", "redis");
      ini_set("session.save_path", savePath);
  }
}
