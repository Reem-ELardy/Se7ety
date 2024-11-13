<?php
class Donor extends Person {
    protected $id;
    protected $personId;

    public function __construct($id = null, $personId = null, $name = "", $age = 0, $password = "", $email = "", $addressId = null, $IsDeleted = false) {
        // Initialize the parent class (Person)
        parent::__construct($id, $name, $age, $password, $email, $addressId, $IsDeleted);
        $this->personId = $personId;
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function createDonar($dbConnection) {
        // First, create the associated Person record
        if ($this->id === null) {
            $personCreated = $this->createPerson($dbConnection);
            if (!$personCreated) {
                return false;
            }
        }

        // Create Donor record
        $query = "INSERT INTO Donor (PersonID) VALUES (?)";
        $stmt = $dbConnection->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $dbConnection->error;
            return false;
        }
        $stmt->bind_param("i", $this->personId);
        $result = $stmt->execute();
        if (!$result) {
            echo "Execute failed: " . $stmt->error;
        } else {
            // Set donor ID (if needed)
            $this->id = $dbConnection->insert_id;
        }
        return $result;
    }

    // Create associated Person record
    public function createPerson($dbConnection) {
        if ($this->id !== null) {
            echo "Error: Cannot create person with an existing ID.";
            return false;
        }

        $query = "INSERT INTO Person (Name, Age, Password, Email, AddressID) VALUES (?, ?, ?, ?, ?)";
        $stmt = $dbConnection->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $dbConnection->error;
            return false;
        }

        // Accessing parent class properties with parent::
        $stmt->bind_param("sisss", $this->name, $this->age, $this->password, $this->email, $this->addressId);
        $result = $stmt->execute();
        if (!$result) {
            echo "Execute failed: " . $stmt->error;
        } else {
            // After person is created, set the ID and personId
            $this->id = $dbConnection->insert_id; // Set the ID to the newly created ID
            $this->personId = $this->id; // Set the personId for the Donor
        }
        return $result;
    }

     // Update Donor record
     public function updateDonor($dbConnection) {
        // Update the Person record (related to the donor)
        $query = "UPDATE Person SET Name = ?, Age = ?, Password = ?, Email = ?, AddressID = ? WHERE ID = ?";
        $stmt = $dbConnection->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $dbConnection->error;
            return false;
        }

        // Bind parameters and execute the update for the person data
        $stmt->bind_param("sisssi", $this->name, $this->age, $this->password, $this->email, $this->addressId, $this->personId);
        $result = $stmt->execute();
        if (!$result) {
            echo "Execute failed: " . $stmt->error;
            return false;
        }

        // Update Donor record if needed (optional, can update other fields in the Donor table)
        $query = "UPDATE Donor SET PersonID = ? WHERE ID = ?";
        $stmt = $dbConnection->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $dbConnection->error;
            return false;
        }

        // Bind parameters and execute the update for the donor data
        $stmt->bind_param("ii", $this->personId, $this->id);
        $result = $stmt->execute();
        if (!$result) {
            echo "Execute failed: " . $stmt->error;
            return false;
        }

        return true;
    }

    public function readDonor($dbConnection, $donorId) {
        // First, load the donor's details based on their ID
        $query = "SELECT Person.ID as PersonID, Person.Name, Person.Age, Person.Password, Person.Email, Person.AddressID, Donor.ID as DonorID
                  FROM Donor 
                  INNER JOIN Person ON Donor.PersonID = Person.ID 
                  WHERE Donor.ID = ?";
        
        $stmt = $dbConnection->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $dbConnection->error;
            return false;
        }
    
        // Bind the donor ID to the query
        $stmt->bind_param("i", $donorId);
        $stmt->execute();
    
        // Bind the result to class properties
        $stmt->bind_result($this->personId, $this->name, $this->age, $this->password, $this->email, $this->addressId, $this->id);
    
        // Fetch the result
        if ($stmt->fetch()) {
            // Successfully loaded the donor data
            return true;
        } else {
            // Donor not found
            return false;
        }
    }
    // public static function deleteDonor($dbConnection, $donorId) {
    //     if ($donorId === null) {
    //         echo "Error: Donor ID is not set.";
    //         return false;
    //     }
    
    //     // Fetch the PersonID associated with the Donor
    //     $personIdQuery = "SELECT PersonID FROM Donor WHERE ID = ?";
    //     $stmt = $dbConnection->prepare($personIdQuery);
        
    //     if (!$stmt) {
    //         echo "Prepare failed: " . $dbConnection->error;
    //         return false;
    //     }
    
    //     $stmt->bind_param("i", $donorId);
    //     $stmt->execute();
    //     $stmt->bind_result($personId);
    
    //     if (!$stmt->fetch()) {
    //         echo "Error: Person ID associated with Donor not found.";
    //         $stmt->close();
    //         return false;
    //     }
    //     $stmt->close();
    
    //     // Now mark the Donor record as deleted
    //     $query = "UPDATE Donor SET IsDeleted = 1 WHERE ID = ?";
    //     $stmt = $dbConnection->prepare($query);
        
    //     if (!$stmt) {
    //         echo "Prepare failed: " . $dbConnection->error;
    //         return false;
    //     }
        
    //     $stmt->bind_param("i", $donorId);
    //     $result = $stmt->execute();
        
    //     if (!$result) {
    //         echo "Execute failed for Donor table: " . $stmt->error;
    //         $stmt->close();
    //         return false;
    //     }
    //     $stmt->close();
    
    //     // Mark the associated Person record as deleted
    //     $query = "UPDATE Person SET IsDeleted = 1 WHERE ID = ?";
    //     $stmt = $dbConnection->prepare($query);
        
    //     if (!$stmt) {
    //         echo "Prepare failed: " . $dbConnection->error;
    //         return false;
    //     }
        
    //     $stmt->bind_param("i", $personId);
    //     $result = $stmt->execute();
        
    //     if (!$result) {
    //         echo "Execute failed for Person table: " . $stmt->error;
    //     } else {
    //         echo "Donor with ID " . $donorId . " and associated Person with ID " . $personId . " marked as deleted.\n";
    //     }
    
    //     $stmt->close();
    //     return $result;
    // }



    public function delete($dbConnection, $donorId) {
    if ($donorId === null) {
        echo "Error: Person ID is not set.";
        return false;
    }

    $query = "UPDATE Person SET IsDeleted = true WHERE ID = ?";
    $stmt = $dbConnection->prepare($query);
    
    if (!$stmt) {
        echo "Prepare failed: " . $dbConnection->error;
        return false;
    }
    
    $stmt->bind_param("i", $this->personId);
    $result = $stmt->execute();
    
    if (!$result) {
        echo "Execute failed: " . $stmt->error;
    } else {
        echo "Donor with ID " . $donorId . " marked as deleted.\n";
    }
    
    return $result;
}
    
}
?>
