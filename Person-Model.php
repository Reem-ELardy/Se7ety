<?php
abstract class Person {
    protected $id;
    protected $name;
    protected $age;
    protected $password;
    protected $email;
    protected $addressId;
    protected $IsDeleted;

    public function __construct($id = null, $name = "", $age = 0, $password = "", $email = "", $addressId = null,$IsDeleted=false) {
        $this->id = $id;
        $this->name = $name;
        $this->age = $age;
        $this->password = $password;
        $this->email = $email;
        $this->addressId = $addressId;
        $this->IsDeleted = $IsDeleted;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getAge() {
        return $this->age;
    }

    public function setAge($age) {
        $this->age = $age;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getAddressId() {
        return $this->addressId;
    }

    public function setAddressId($addressId) {
        $this->addressId = $addressId;
    }
    
    public function setIsDeleted($IsDeleted) {
        $this->IsDeleted = $IsDeleted;
    }
    public function getIsDeleted($IsDeleted) {
        return $this->IsDeleted;    }
    
    abstract public function createPerson($dbConnection);
    
    // public function createPerson($dbConnection) {
    //     if ($this->id !== null) {
    //         echo "Error: Cannot create person with an existing ID.";
    //         return false;
    //     }
    //     $query = "INSERT INTO Person (Name, Age, Password, Email, AddressID) VALUES (?, ?, ?, ?, ?)";
    //     $stmt = $dbConnection->prepare($query);
    //     if (!$stmt) {
    //         echo "Prepare failed: " . $dbConnection->error;
    //         return false;
    //     }
    //     $stmt->bind_param("sisss", $this->name, $this->age, $this->password, $this->email, $this->addressId);
    //     $result = $stmt->execute();
    //     if (!$result) {
    //         echo "Execute failed: " . $stmt->error;
    //     } else {
    //         $this->id = $dbConnection->insert_id; // Set the ID to the newly created ID
    //     }
    //     return $result;
    // }
    
    // public function update($dbConnection) {
    //     if ($this->id === null) {
    //         echo "Error: Cannot update person without an existing ID.";
    //         return false;
    //     }
    //     $query = "UPDATE Person SET Name = ?, Age = ?, Password = ?, Email = ?, AddressID = ? WHERE ID = ?";
    //     $stmt = $dbConnection->prepare($query);
    //     if (!$stmt) {
    //         echo "Prepare failed: " . $dbConnection->error;
    //         return false;
    //     }
    //     $stmt->bind_param("sisssi", $this->name, $this->age, $this->password, $this->email, $this->addressId, $this->id);
    //     $result = $stmt->execute();
    //     if (!$result) {
    //         echo "Execute failed: " . $stmt->error;
    //     }
    //     return $result;
    // }
    
    
    // public static function read($dbConnection, $id) {
    //     $query = "SELECT * FROM Person WHERE ID = ? AND IsDeleted = 0";
    //     $stmt = $dbConnection->prepare($query);
        
    //     if (!$stmt) {
    //         echo "Prepare failed: " . $dbConnection->error;
    //         return null;
    //     }
        
    //     $stmt->bind_param("i", $id);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
        
    //     if ($row = $result->fetch_assoc()) {
    //         return new self($row['ID'], $row['Name'], $row['Age'], $row['Password'], $row['Email'], $row['AddressID'], $row['IsDeleted']);
    //     }
    //     return null;
    // }

    // public static function delete($dbConnection, $id) {
    //     if ($id === null) {
    //         echo "Error: Person ID is not set.";
    //         return false;
    //     }
    
    //     $query = "UPDATE Person SET IsDeleted = 1 WHERE ID = ?";
    //     $stmt = $dbConnection->prepare($query);
        
    //     if (!$stmt) {
    //         echo "Prepare failed: " . $dbConnection->error;
    //         return false;
    //     }
        
    //     $stmt->bind_param("i", $id);
    //     $result = $stmt->execute();
        
    //     if (!$result) {
    //         echo "Execute failed: " . $stmt->error;
    //     } else {
    //         echo "Person with ID " . $id . " marked as deleted.\n";
    //     }
        
    //     return $result;
    // }
        
}
?>
