<?php
require_once"../DB-creation\DB-Connection.php";

class Address {
    // Attributes
    public $ID;
    public $Name;
    public $ParentAddressID;
    protected $IsDeleted;



    public function create($name, $parentAddressID = null) {
        $dbConnection = DBConnection::getInstance()->getConnection();

        $this->Name = $name;
        $this->ParentAddressID = $parentAddressID; // Set the ParentAddressID (can be NULL)
    
        // Modify the SQL query to use ? placeholders instead of named parameters
        $query = "INSERT INTO address (Name, ParentAddressID) VALUES (?, ?)";
        
        // Prepare the query
        $stmt = $dbConnection->prepare($query);
    
        // If ParentAddressID is null, bind it as NULL
        $stmt->bind_param('si', $this->Name, $this->ParentAddressID);
    
        // Execute the query
        if ($stmt->execute()) {
            $this->ID = $dbConnection->insert_id; // Get the last inserted ID
            return true;
        }
        return false;
    }

    public function getParentAddressID()
    {
        return $this->ParentAddressID;
    }
    
    

    public function read($id) {
        $dbConnection = DBConnection::getInstance()->getConnection();

        // Query with a positional placeholder for ID
        $query = "SELECT * FROM address WHERE ID = ?";
    
        // Prepare the query
        $stmt = $dbConnection->prepare($query);
    
        // Check if the statement was prepared successfully
        if (!$stmt) {
            die('Error preparing statement: ' . $dbConnection->error);
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
        $dbConnection = DBConnection::getInstance()->getConnection();

        // If a new ParentAddressID is provided, use it, otherwise keep the existing one
        if ($parentAddressID !== null) {
            $query = "UPDATE address SET Name = ?, ParentAddressID = ? WHERE ID = ?";
            $stmt = $dbConnection->prepare($query);
            // Bind the parameters: 's' for string (name), 'i' for integer (parent address ID and ID)
            $stmt->bind_param('sii', $name, $parentAddressID, $this->ID);
        } else {
            // If no ParentAddressID is provided, keep the existing one
            $query = "UPDATE address SET Name = ? WHERE ID = ?";
            $stmt = $dbConnection->prepare($query);
            // Bind only the name and ID
            $stmt->bind_param('si', $name, $this->ID);
        }

        // Execute the query and return the result
        return $stmt->execute();
    }



    // Delete an address by ID
    public function delete() {
        $dbConnection = DBConnection::getInstance()->getConnection();

        // Query with a positional placeholder for ID
        $this->IsDeleted = true;
    
        $query = "UPDATE address SET IsDeleted = 1 WHERE ID = ?";
    
        // Prepare the query
        $stmt = $dbConnection->prepare($query);
    
        if (!$stmt) {
            die('Error preparing statement: ' . $dbConnection->error);
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
        $dbConnection = DBConnection::getInstance()->getConnection();
    
        
        $query = "SELECT * FROM Address WHERE ParentAddressID IS NULL";
    
        
        $stmt = $dbConnection->prepare($query);
    
        
        if (!$stmt) {
            die('Error preparing statement: ' . $dbConnection->error);
        }
    
   
        $stmt->execute();
    
        
        $result = $stmt->get_result();
    
        if (!$result || $result->num_rows === 0) {
            $stmt->close();
            return false; 
        }
    
        
        while ($row = $result->fetch_assoc()) {
            if($row['IsDeleted']==1){continue;};
            $addressList[$row['ID']] = $row;
            $addressList[$row['ID']]['children'] = [];
        }
    
        $stmt->close(); 
    
        
        
        $addressList = array_values($addressList);

        for ($i = 0; $i < count($addressList);  $i++) {
           
            $parentId = $addressList[$i]['ID'];
        
            $query = "SELECT * FROM Address WHERE ParentAddressID = ?";
    
           
            $stmt = $dbConnection->prepare($query);
    

            if (!$stmt) {
                die('Error preparing statement: ' . $dbConnection->error);
            }
    
            
            $stmt->bind_param('i', $parentId);
    
           
            $stmt->execute();
    
           
            $childResult = $stmt->get_result();
    
            if ($childResult && $childResult->num_rows > 0) {
                while ($childRow = $childResult->fetch_assoc()) {
                    if($childRow['IsDeleted']==1){continue;};
                    $addressList[$i]['children'][] = $childRow; 
                }
            }
    
            $stmt->close(); 
        }
    
        return true; 
    }
    
         




}


?>
