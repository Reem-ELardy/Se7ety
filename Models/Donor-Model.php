<?php
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';
require_once 'Person-Model.php';

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
        if ($this->id === null) {
            $personId = $this->createPerson(); // Get the Person ID
            if (!$personId) {
                return false;
            }
            $this->personId = $personId;  // Assign the returned Person ID
        }
    
        $query = "INSERT INTO Donor (PersonID) VALUES (?)";
        $stmt = $this->dbProxy->prepare($query, [$this->personId]);
        if (!$stmt) {
            return false;
        }
    
        $this->id = $this->dbProxy->getInsertId(); // Get the Donor ID
    
        return true;
    }

    public function login($email, $enteredPassword) {
        $email = trim($email);

        $query = "SELECT Person.ID as PersonID, Person.Name, Person.Age, Person.Password, Person.Email, Person.AddressID, Donor.ID as DonorID
                  FROM Donor 
                  INNER JOIN Person ON Donor.PersonID = Person.ID 
                  WHERE Person.Email = ? 
                  ORDER BY Person.ID DESC
                  LIMIT 1";

        $stmt = $this->dbProxy->prepare($query, [$email]); // Prepare statement using DBProxy
        if (!$stmt) {
            return false;
        }

        // Bind the result variables
        $stmt->bind_result($this->personId, $this->name, $this->age, $this->password, $this->email, $this->addressId, $this->id);
        
        // Fetch and validate login
        if ($stmt->fetch() && $enteredPassword === $this->password) {
            return true;
        }
        return false;
    }

    public function signup($name, $age, $password, $email) {
        // Input validation (you can expand this to include more robust checks)
        if (empty($name) || empty($age) || empty($password) || empty($email)) {
            return false;
        }

        // Check if the email already exists
        $IsPersonExist = $this->findByEmail($email);
        if ($IsPersonExist) {
            return false;
        } else {
            // Set class properties
            $this->name = $name;
            $this->age = $age;
            $this->password = $password;
            $this->email = $email;

            // Use the createDonor method to add the new user to the database
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
        // Update the Person record (related to the donor)
        $personUpdated = $this->updatePerson([$this->name, $this->age, $this->password, $this->email, $this->addressId, $this->personId]);

        if (!$personUpdated) {
            return false;  // Person update failed
        }
        // Update Donor record if needed (optional, can update other fields in the Donor table)
        $query = "UPDATE Donor SET PersonID = ? WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->personId, $this->id]);
        if (!$stmt) {
            return false;
        }

        return true;
    }

    public function readDonor($donorId) {
        // First, load the donor's details based on their ID
        $query = "SELECT Person.ID as PersonID, Person.Name, Person.Age, Person.Password, Person.Email, Person.AddressID, Donor.ID as DonorID
                  FROM Donor 
                  INNER JOIN Person ON Donor.PersonID = Person.ID 
                  WHERE Donor.ID = ?";

        $stmt = $this->dbProxy->prepare($query, [$donorId]); // Prepare statement using DBProxy
        if (!$stmt) {
            return false;
        }

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
        if ($donorId === null) {
            return false;
        }

        // Delete (mark as deleted) the donor record
        $query = "UPDATE Person SET IsDeleted = true WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->personId]);
        if (!$stmt) {
            return false;
        }

        return true;
    }

    public function findByEmail($email) {
        $email = trim($email);

        $query = "SELECT Person.ID as PersonID, Person.Email, Donor.ID as DonorID, Person.IsDeleted
                  FROM Donor 
                  INNER JOIN Person ON Donor.PersonID = Person.ID 
                  WHERE Person.Email = ? 
                  AND Person.IsDeleted = 0
                  ORDER BY Person.ID DESC
                  LIMIT 1";

        $stmt = $this->dbProxy->prepare($query, [$email]);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_result($this->personId, $this->email, $this->id, $this->IsDeleted);

        if ($stmt->fetch()) {
            if ($this->IsDeleted) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
}
?>