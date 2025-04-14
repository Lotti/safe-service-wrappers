namespace SafeServiceWrapper;

/**
 * Mysqli wrapper that fetches the password from CyberArk.
 * Extends the native mysqli class.
 */
class MySQLi extends \mysqli
{
    /**
     * Constructor: fetches password from CyberArk and calls parent constructor.
     *
     * @param string host
     * @param string user
     * @param string dbname
     * @param int port
     * @param string socket
     *
     * @throws Exception if connection fails or CyberArk retrieval fails.
     */
    public function __construct(string host, string user, string dbname, int port = 3306, string socket = "") {
        var password, error;
        try {
            // Fetch password from CyberArk
            let password = CyberarkClient::fetchPassword(host, port, user)["password"];
        } catch \Exception, error {
            throw new \Exception("Failed to fetch password from CyberArk: " . error->getMessage());
        }

        // Call the parent constructor with the fetched password
        parent::__construct(host, user, password, dbname, port, socket);

        // Check for connection errors
        if (this->connect_error) {
            throw new \Exception("mysqli connection failed: " . this->connect_error);
        }
    }
}
