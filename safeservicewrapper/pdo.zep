namespace SafeServiceWrapper;

/**
 * PDO wrapper that fetches the password from CyberArk.
 * Extends the native PDO class.
 */
class Pdo extends \PDO {
    /**
     * Constructor: fetches password from CyberArk and calls parent constructor.
     *
     * @param string dsn
     * @param string username
     * @param string password
     * @param array options = []
     *
     * @throws \Exception if connection fails or CyberArk retrieval fails.
     */
    public function __construct(string dsn, string username, string password, array options = []) {
        var cyberarkResult, cyberark_password, error;
        var endpoint = this->explodeDsn(dsn);
        try {
            // Fetch password from CyberArk
            let cyberarkResult = CyberarkClient::fetchPassword(endpoint["host"], endpoint["port"], username);
            let cyberark_password = cyberarkResult["password"];
        } catch \Exception, error {
            throw new \Exception("Failed to fetch password from CyberArk: " . error->getMessage());
        }

        // Construct the DSN string

        try {
            // Call the parent constructor with the fetched password
            // Zephir requires explicit passing of parameters for parent::__construct
            parent::__construct(dsn, username, cyberark_password, options);

            // Set PDO attributes here if needed (e.g., error mode)
            // Note: Zephir might handle attribute setting differently or require specific syntax
            // Depending on Zephir version and PDO integration, direct setAttribute might work
            // or might need alternative approach if extending internal classes has limitations.
            // For now, assuming direct call works:
            // this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        } catch \Exception, error {
            // Catch potential PDOException specifically if possible and rethrow
            throw new \Exception("PDO connection failed: " . error->getMessage());
        }
    }

    /**
     * @param string dsn
     * @return array ["driver": string, "host": string, "dbname": string, "port": int]
     */
    private function explodeDsn(string dsn) -> array {
        // Split into type and parameters
        var parts = explode(":", dsn, 2);
        if count(parts) < 2 {
            throw new \Exception("Invalid DSN string");
        }

        array result = [];
        let result["driver"] = parts[0];

        // Split parameters by semicolon
        var keyValue;
        for keyValue in explode(";", parts[1]) {
            var pair = explode("=", keyValue, 2);
            if count(pair) == 2 {
                string key = trim(pair[0]), value = trim(pair[1]);
                let result[key] = value;
            }
        }

        if isset result["port"] {
            let result["port"] = (int) result["port"];
        }

        return result;
    }
}
