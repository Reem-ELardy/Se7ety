<?php
require_once __DIR__ . '/IDatabase.php';
require_once __DIR__ . '/DB-Connection.php';

class DBProxy implements IDatabase
{
    private $dbConnection;
    private $IsAdmin;

    public function __construct(String $username = null)
    {
        // Set default value for IsAdmin
        $this->IsAdmin = false;

        // If a username is provided, set IsAdmin based on the username
        if ($username !== null) {
            $this->IsAdmin = str_contains($username, "Admin");
        }

        // Use the singleton instance of DBConnection
        $this->dbConnection = DBConnection::getInstance();
    }

    public function run_query(string $query): mysqli_result|bool
    {
        if ($this->IsAdmin) {
            return $this->dbConnection->run_query($query);
        } else {
            if (stripos(trim($query), 'delete') === 0) {
                return false;
            } else {
                return $this->dbConnection->run_query($query);
            }
        }
    }

    function run_queries($queries): array
    {
        $conn = DBConnection::getInstance()->getConnection();
        $ret = [];
        foreach ($queries as $query) {
            $ret[] = $this->run_query($query);        }
        return $ret;
    }

    public function prepare(string $query, array $params): mysqli_stmt|bool
    {
        if ($this->IsAdmin) {
            return $this->dbConnection->prepare($query, $params);
        } else {
            if (stripos(trim($query), 'delete') === 0) {
                return false;
            } else {
                return $this->dbConnection->prepare($query, $params);
            }
        }
    }

    public function getConnection()
    {
        return $this->dbConnection->getConnection();
    }

    public function getInsertId() {
        return $this->dbConnection->getInsertId();
    }
}
?>