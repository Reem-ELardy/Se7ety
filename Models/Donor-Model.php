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

    public function createDonor() {
        $conn = DBConnection::getInstance()->getConnection();
        if ($this->id === null) {
            $personCreated = $this->createPerson();
            if (!$personCreated) {
                return false;
            }
        }

        $query = "INSERT INTO Donor (PersonID) VALUES (?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $this->id);
        $result = $stmt->execute();
        if ($result) {
            $this->personId = $this->id;
            $this->id = $conn->insert_id;
        }
        return $result;
    }


    public function login($email, $enteredPassword) {
        $conn = DBConnection::getInstance()->getConnection();

        $email = trim($email);
        $query = "SELECT Person.ID as PersonID, Person.Name, Person.Age, Person.Password, Person.Email, Person.AddressID, Donor.ID as DonorID, Person.IsDeleted
                  FROM Donor 
                  INNER JOIN Person ON Dnor.PersonID = Person.ID 
                  WHERE Person.Email = ?
                  ORDER BY Person.ID DESC
                  LIMIT 1";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            return false;
        }

        $stmt->bind_result($this->personId, $this->name, $this->age, $this->password, $this->email, $this->addressId, $this->id, $this->IsDeleted);
        if ($stmt->fetch() && $enteredPassword === $this->password && !$this->IsDeleted) {
            return true;
        }
        return false;
    }
    
    //public function signup($name, $age, $password, $email, $addressId)
    public function signup($name, $age, $password, $email) {
        // Input validation (you can expand this to include more robust checks)
        if (empty($name) || empty($age) || empty($password) || empty($email)) {
            return false;
        }
        $IsPersonExist= $this-> findByEmail($email);
        if ($IsPersonExist) {
            return false;
        }
        else {
                    // Set class properties
            $this->name = $name;
            $this->age = $age;
            $this->password = $password;
            $this->email = $email;
        
            // Use the createPerson method to add the new user to the database
            $result = $this->createDonor();
        
            if ($result) {
                return true;
            } else {
                return false;
            }
        }
    

    }

    // Update Donor record
    public function updateDonor() {
        $conn = DBConnection::getInstance()->getConnection();

        // Update the Person record (related to the donor)
        $query = "UPDATE Person SET Name = ?, Age = ?, Password = ?, Email = ?, AddressID = ? WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        // Bind parameters and execute the update for the person data
        $stmt->bind_param("sisssi", $this->name, $this->age, $this->password, $this->email, $this->addressId, $this->personId);
        $result = $stmt->execute();
        if (!$result) {
            return false;
        }

        // Update Donor record if needed (optional, can update other fields in the Donor table)
        $query = "UPDATE Donor SET PersonID = ? WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        // Bind parameters and execute the update for the donor data
        $stmt->bind_param("ii", $this->personId, $this->id);
        $result = $stmt->execute();
        if (!$result) {
            return false;
        }

        return true;
    }

    public function readDonor($donorId) {
        $conn = DBConnection::getInstance()->getConnection();

        // First, load the donor's details based on their ID
        $query = "SELECT Person.ID as PersonID, Person.Name, Person.Age, Person.Password, Person.Email, Person.AddressID, Donor.ID as DonorID
                  FROM Donor 
                  INNER JOIN Person ON Donor.PersonID = Person.ID 
                  WHERE Donor.ID = ?";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
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

    public function delete($donorId) {
        $conn = DBConnection::getInstance()->getConnection();

        if ($donorId === null) {
            return false;
        }

        $query = "UPDATE Person SET IsDeleted = true WHERE ID = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
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


    public function findByEmail($email) {
        
       
        $conn = DBConnection::getInstance()->getConnection();
    
       
        $email = trim($email);
    
        
        $query = "SELECT Person.ID as PersonID, Person.Email, Person.IsDeleted, Donor.ID as DonorID
                  FROM Donor 
                  INNER JOIN Person ON Donor.PersonID = Person.ID 
                  WHERE Person.Email = ? ";
    
       
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false; 
        }
        
        $stmt->bind_param("s", $email);
    
        if (!$stmt->execute()) {
            return false; 
        }
    
        $stmt->bind_result($this->personId, $this->email,$this->IsDeleted, $this->id);
    
        if ($stmt->fetch()) {
            if ($this->IsDeleted) {
                return false;
            }
            else {
                return true;
            }
        } else {
            return false;
        }
    }
}
?>
