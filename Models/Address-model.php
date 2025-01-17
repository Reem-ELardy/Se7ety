<?php
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';


class Address {
    // Attributes
    public $ID;
    public $Name;
    public $ParentAddressID;
    protected $IsDeleted;
    private $dbProxy;

    private function getDbProxy() {
        if ($this->dbProxy === null) {
            $this->dbProxy = new DBProxy('user');
        }
        return $this->dbProxy;
    }


    public function create($name, $parentAddressID = null) {
        $this->Name = $name;
        $this->ParentAddressID = $parentAddressID; // Set the ParentAddressID (can be NULL)
    
        // Modify the SQL query to use ? placeholders instead of named parameters
        $query = "INSERT INTO address (Name, ParentAddressID) VALUES (?, ?)";

        $stmt = $this->getDbProxy()->prepare($query, [$this->Name, $this->ParentAddressID]);

        // Execute the query
        if ($stmt) {
            $this->ID = $this->getDbProxy()->getInsertId(); // Get the last inserted ID
            return true;
        }
        return false;
    }

    public function getParentAddressID()
    {
        return $this->ParentAddressID;
    }
    

    public function read($id) {
        // Query with a positional placeholder for ID
        $query = "SELECT * FROM address WHERE ID = ?";
    
        // Prepare the query
        $stmt = $this->getDbProxy()->prepare($query, [$id]);
    
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
            $stmt = $this->getDbProxy()->prepare($query, [$name, $parentAddressID, $this->ID]);
        } else {
            // If no ParentAddressID is provided, keep the existing one
            $query = "UPDATE address SET Name = ? WHERE ID = ?";
            $stmt = $this->getDbProxy()->prepare($query, [$name, $this->ID]);
        }

        // Execute the query and return the result
        return $stmt;
    }

    // Delete an address by ID
    public function delete() {
        // Query with a positional placeholder for ID
        $this->IsDeleted = true;
    
        $query = "UPDATE address SET IsDeleted = 1 WHERE ID = ?";
        $stmt = $this->getDbProxy()->prepare($query, [$this->ID]);
    
        // Execute the query
        if ($stmt) {
            return true;
        } else {
            return false;
        }
    }

    public function GetWholeAddress($id, & $wholeAddress){
        if($this->read($id)){
            if($this->ParentAddressID != null){
                $wholeAddress = $wholeAddress . ", " . $this->Name;
                $this-> GetWholeAddress($this->ParentAddressID, $wholeAddress);
            }
            else {
                $wholeAddress = $wholeAddress . ", " . $this->Name.".";
                return $wholeAddress;
            }
        }
        else {
            return false;
        }
    }

    public function GetWholeAddressesList(&$addressList) {    
        $query = "SELECT * FROM Address WHERE ParentAddressID IS NULL";
        $stmt = $this->getDbProxy()->prepare($query, []);    
        
        $result = $stmt->get_result();
    
        if (!$result || $result->num_rows === 0) {
            return false; 
        }
    
        while ($row = $result->fetch_assoc()) {
            if($row['IsDeleted']==1){continue;};
            $addressList[$row['ID']] = $row;
            $addressList[$row['ID']]['children'] = [];
        }
    
        $addressList = array_values($addressList);

        for ($i = 0; $i < count($addressList);  $i++) {
           
            $parentId = $addressList[$i]['ID'];
        
            $query = "SELECT * FROM Address WHERE ParentAddressID = ?";
            $stmt = $this->getDbProxy()->prepare($query, [$parentId]);    
           
            $childResult = $stmt->get_result();
    
            if ($childResult && $childResult->num_rows > 0) {
                while ($childRow = $childResult->fetch_assoc()) {
                    if($childRow['IsDeleted']==1){continue;};
                    $addressList[$i]['children'][] = $childRow; 
                }
            }
    
        }
    
        return true; 
    }
}
?>
