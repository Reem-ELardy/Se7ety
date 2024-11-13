<?php

CLass DBConnection
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

    private function database_connect($database_host, $database_username, $database_password,$db_name) {
        if ($connection = mysqli_connect($database_host, $database_username, $database_password,$db_name)) {
        
                
            return $connection;
            
        } else {
                die("Database connection error");
            
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
}


/* $servername = "localhost";
$username = "root";
$conn = new mysqli($servername, $username);
$dbname = "Se7ety";


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} */

//echo "Connected successfully<br/><hr/>";

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

function run_query($query, $echo = false): bool
{
    return run_queries([$query], $echo)[0];
}

function run_select_query($query, $echo = false): mysqli_result|bool
{
    $conn = DBConnection::getInstance()->getConnection();
    $result = $conn->query($query);
    /* if ($echo) {
        echo '<pre>' . $query . '</pre>';
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc())
                echo $row;
        } else {
            echo "0 results";
        }
        echo "<hr/>";
    } */
    return $result;
}

// $conn->close();
?>
