namespace SafeServiceWrapper;

class Redis extends \Redis {
  private function getCredentials(mixed credentials) -> mixed {
    var env_password;
    let env_password = getenv("PASSWORD");
    array array_credentials;
    if is_array(credentials) {
      if count(credentials) === 2 {
        let array_credentials[1] = env_password;
      } else {
        let array_credentials[0] = env_password;
      }
      return array_credentials;
    } else {
      return env_password;
    }
  }

  public function __construct(array! options = []) {
    let options["auth"] = this->getCredentials(options["auth"]);
    parent::__construct(options);
  }

  public function auth(mixed credentials) -> boolean {
    return parent::auth(this->getCredentials(credentials));
  }

  public function getAuth() -> mixed {
    return null;
  }
}
