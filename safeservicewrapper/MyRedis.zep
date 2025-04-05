namespace SafeServiceWrapper;

use Redis;

class MyRedis extends Redis {
  private function getCredentials(mixed credentials) -> string|array {
    let env_password = getenv("PASSWORD");
    if is_array(credentials) {
      if count(credentials) === 2 {
        let credentials[1] = env_password;
      } else {
        let credentials[0] = env_password;
      }
    } else {
      let credentials = env_password;
    }

    return credentials;
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
