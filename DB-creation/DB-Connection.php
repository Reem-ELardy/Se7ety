<?php
require_once __DIR__ . '/IDatabase.php';

class DBConnection implements IDatabase 
{
    private static $instance;
    private $connection;

    private function __construct()
    {
        $host = "localhost";
        $username = "root";
        $password = "";
        $dbname = "Se7ety";

        $this->connection = $this->database_connect($host, $username, $password, $dbname);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DBConnection();
        }
        return self::$instance;
    }

    private function database_connect($database_host, $database_username, $database_password, $db_name) {
        $connection = mysqli_connect($database_host, $database_username, $database_password, $db_name);
        if ($connection) {
            return $connection;
        } else {
            die("Database connection error");
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function run_query(string $query): mysqli_result|bool
    {
        return $this->connection->query($query);
    }

    public function prepare(string $query, array $params): mysqli_stmt|bool
    {
        $stmt = $this->connection->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->connection->error . "\nQuery: " . $query);
        }

        // Bind parameters if any
        if (!empty($params)) {
            $types = $this->getParamTypes($params);
            if ($stmt->bind_param($types, ...$params) === false) {
                throw new Exception("Failed to bind parameters: " . $this->connection->error);
            }
        }
        // Execute the statement
        if ($stmt->execute() === false) {
            throw new Exception("Failed to execute statement: " . $this->connection->error);
        }
        return $stmt;
    }

    private function getParamTypes(array $params): string
    {
        $types = '';
        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i'; 
            } elseif (is_float($param)) {
                $types .= 'd'; 
            } elseif (is_string($param)) {
                $types .= 's'; 
            } elseif (is_null($param)) {
                $types .= 's'; 
            } else {
                throw new Exception("Unsupported parameter type: " . gettype($param) . " - Value: " . var_export($param, true));
            }
        }
        return $types;
    }


    public function getInsertId() {
        return $this->connection->insert_id;
    }
}

function run_queries_create_DB($queries, $echo = false): array
{
    $host = "localhost";
    $username = "root";
    $password = "";
    $conn = mysqli_connect($host, $username, $password);
    $ret = [];
    foreach ($queries as $query) {
        $ret[] = $conn->query($query);
        if ($echo) {
            print($ret[array_key_last($ret)] === TRUE ? "Query ran successfully<br/>" : "Error: " . $conn->error);
        }
    }
    mysqli_close($conn);

    return $ret;
}

function run_queries($queries, $echo = false): array
{
    $conn = DBConnection::getInstance()->getConnection();
    $ret = [];
    foreach ($queries as $query) {
        $ret += [$conn->query($query)];
        if ($echo) {
            print($ret[array_key_last($ret)] === TRUE ? "Query ran successfully<br/>" : "Error: " . $conn->error);
        }
    }
    return $ret;
}


?>
