<?php

class Address {
    protected static $dbConnection; // Static database connection property

    // Attributes
    public $ID;
    public $Name;
    public $ParentAddressID;
    protected $isDeleted;

    // Set the database connection
    public static function setDbConnection($connection) {
        self::$dbConnection = $connection;
    }

    // Get the database connection
    protected function getDbConnection() {
        if (self::$dbConnection === null) {
            throw new Exception("Database connection is not set.");
        }
        return self::$dbConnection;
    }

    // Constructor to initialize the database connection
    public function __construct() {
        // Ensure the DB connection is set before using it
        $this->getDbConnection();
    }

    public function create($name, $parentAddressID = null) {
        $this->Name = $name;
        $this->ParentAddressID = $parentAddressID; // Set the ParentAddressID (can be NULL)
    
        // Modify the SQL query to use ? placeholders instead of named parameters
        $query = "INSERT INTO address (Name, ParentAddressID) VALUES (?, ?)";
        
        // Prepare the query
        $stmt = self::$dbConnection->prepare($query);
    
        // If ParentAddressID is null, bind it as NULL
        $stmt->bind_param('si', $this->Name, $this->ParentAddressID);
    
        // Execute the query
        if ($stmt->execute()) {
            $this->ID = self::$dbConnection->insert_id; // Get the last inserted ID
            return true;
        }
        return false;
    }
    
    

    public function read($id) {
        // Query with a positional placeholder for ID
        $query = "SELECT * FROM address WHERE ID = ?";
    
        // Prepare the query
        $stmt = self::$dbConnection->prepare($query);
    
        // Check if the statement was prepared successfully
        if (!$stmt) {
            die('Error preparing statement: ' . self::$dbConnection->error);
        }
    
        // Bind the parameter: 'i' for integer (for ID)
        $stmt->bind_param('i', $id);
    
        // Execute the statement
        $stmt->execute();
    
        // Get the result
        $result = $stmt->get_result();
    
        // Fetch the row
        $row = $result->fetch_assoc();
    
        if ($row) {
            $this->ID = $row['ID'];
            $this->Name = $row['Name'];
            $this->ParentAddressID = $row['ParentAddressID'];
            return true;
        }
    
        return false;
    }
    

    public function update($name, $parentAddressID = null) {
        // If a new ParentAddressID is provided, use it, otherwise keep the existing one
        if ($parentAddressID !== null) {
            $query = "UPDATE address SET Name = ?, ParentAddressID = ? WHERE ID = ?";
            $stmt = self::$dbConnection->prepare($query);
            // Bind the parameters: 's' for string (name), 'i' for integer (parent address ID and ID)
            $stmt->bind_param('sii', $name, $parentAddressID, $this->ID);
        } else {
            // If no ParentAddressID is provided, keep the existing one
            $query = "UPDATE address SET Name = ? WHERE ID = ?";
            $stmt = self::$dbConnection->prepare($query);
            // Bind only the name and ID
            $stmt->bind_param('si', $name, $this->ID);
        }
    
        // Execute the query and return the result
        return $stmt->execute();
    }
    

    public function delete() {

        $this->isDeleted = true;
    
        $query = "UPDATE address SET isDeleted = 1 WHERE ID = ?";
    
        // Prepare the query
        $stmt = self::$dbConnection->prepare($query);
    
        if (!$stmt) {
            die('Error preparing statement: ' . self::$dbConnection->error);
        }
    
        // Bind the parameter: 'i' for integer (for ID)
        $stmt->bind_param('i', $this->ID);
    
        // Execute the query
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    
}
?>
