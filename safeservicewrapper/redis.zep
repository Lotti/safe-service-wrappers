namespace SafeServiceWrapper;

class Redis extends \Redis {
  private function getCredentials(var credentials) -> mixed {
    var env_password;
    let env_password = getenv("PASSWORD");
    if is_array(credentials) {
      return [credentials[0], env_password];
    } else {
      return env_password;
    }
  }

  public function __construct(array options = []) {
    if isset options["auth"] {
      let options["auth"] = this->getCredentials(options["auth"]);  
    } else {
      let options["auth"] = this->getCredentials(null);
    }
    parent::__construct(options);
  }

  public function auth(var credentials) -> boolean {
    return parent::auth(this->getCredentials(credentials));
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
   * - safeservicewrapper.session_auth
   * - safeservicewrapper.session_prefix
   * - safeservicewrapper.session_timeout
   * - safeservicewrapper.session_database
   * - safeservicewrapper.session_persistent
   * - safeservicewrapper.session_lock_retries
   * - safeservicewrapper.session_lock_wait_time
   */
  public static function configureSessionHandling() -> void
  {
      string host, auth, prefix, savePath;
      int port, timeout, database, persistent, lockRetries, lockWaitTime;
      array parts;

      // Read settings from INI, providing defaults
      let host = ini_get("safeservicewrapper.session_host") ?: "127.0.0.1";
      let port = (int)(ini_get("safeservicewrapper.session_port") ?: 6379);
      let auth = ini_get("safeservicewrapper.session_auth") ?: ""; // Empty means no auth
      let prefix = ini_get("safeservicewrapper.session_prefix") ?: "PHPREDIS_SESSION:";
      let timeout = (int)(ini_get("safeservicewrapper.session_timeout") ?: 0); // 0 means use default php.ini session.gc_maxlifetime
      let database = (int)(ini_get("safeservicewrapper.session_database") ?: 0);
      let persistent = (int)(ini_get("safeservicewrapper.session_persistent") ?: 0); // 0 = non-persistent
      let lockRetries = (int)(ini_get("safeservicewrapper.session_lock_retries") ?: -1); // -1 = use Redis default
      let lockWaitTime = (int)(ini_get("safeservicewrapper.session_lock_wait_time") ?: 2000); // Default 2000 microseconds

      // Construct the save_path string
      // Format: tcp://host:port?auth=password&prefix=prefix&timeout=timeout&database=db&persistent=1&retry_interval=10&read_timeout=2
      // Note: phpredis uses 'auth' not 'password' in the query string
      let savePath = "tcp://" . host . ":" . port;
      let parts = [];

      if !empty auth {
          let parts[] = "auth=" . urlencode(auth); // URL-encode password just in case
      }
      if !empty prefix {
          let parts[] = "prefix=" . urlencode(prefix);
      }
      if timeout > 0 {
          let parts[] = "timeout=" . timeout;
      }
      if database > 0 {
          let parts[] = "database=" . database;
      }
      if persistent > 0 {
          let parts[] = "persistent=1";
      }
      // Add locking parameters if needed (check phpredis documentation for exact names if different)
      // if lockRetries >= 0 {
      //     let parts[] = "lock_retries=" . lockRetries;
      // }
      // if lockWaitTime > 0 {
      //     let parts[] = "lock_wait_time=" . lockWaitTime;
      // }


      if count(parts) > 0 {
          let savePath .= "?" . implode("&", parts);
      }

      // Set the INI settings
      ini_set("session.save_handler", "redis");
      ini_set("session.save_path", savePath);
  }
}
