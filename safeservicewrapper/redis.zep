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
}
