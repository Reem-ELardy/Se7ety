<?php
class Patient extends Person {
    protected $id;
    protected $personId;
    protected $medicalHistory;
    protected $needs;
    protected $nationalId;
    protected $needslist=[];

    public function __construct($id = null, $personId = null, $name = "", $needs = "", $nationalId = null,
                                 $age = 0,  $medicalHistory = "", $needslist = [],
                                 $password = "", $email = "", $addressId = null, $IsDeleted = false) {
        // Initialize the parent class (Person)
        parent::__construct($id, $name, $age, $password, $email, $addressId, $IsDeleted);
        $this->personId = $personId;
        $this->medicalHistory = $medicalHistory;
        $this->needs = $needs;
        $this->needslist = $needslist;
        $this->nationalId = $nationalId;
    }

    // Getters and Setters for Volunteer attributes
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }
     // Getter and Setter for medicalHistory
     public function getMedicalHistory() {
        return $this->medicalHistory;
    }

    public function setMedicalHistory($medicalHistory) {
        $this->medicalHistory = $medicalHistory;
    }

    // Getter and Setter for needs
    public function getNeeds() {
        return $this->needs;
    }

    public function setNeeds($needs) {
        $this->needs = $needs;
    }

    // Getter and Setter for nationalId
    public function getNationalId() {
        return $this->nationalId;
    }

    public function setNationalId($nationalId) {
        $this->nationalId = $nationalId;
    }

    // Getter and Setter for needslist
    public function getNeedslist() {
        return $this->needslist;
    }

    public function setNeedslist($needslist) {
        $this->needslist = $needslist;
    }

    public function createPatient($dbConnection) {
        // First, create the associated Person record
        if ($this->id === null) {
            $personCreated = $this->createPerson($dbConnection);
            if (!$personCreated) {
                return false;
            }
        }

        // Create Volunteer record
        $query = "INSERT INTO Patient (PersonID) VALUES (?)";
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
            $this->id = $dbConnection->insert_id;
        }
        return $result;
    }

    public function updatePatient($dbConnection) {
        // Update the Person record (related to the volunteer)
        $query = "UPDATE Person SET Name = ?, Age = ?, Password = ?, Email = ?, AddressID = ? WHERE ID = ?";
        $stmt = $dbConnection->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $dbConnection->error;
            return false;
        }

        // Bind parameters and execute the update for the person data
        $stmt->bind_param("sissii", $this->name, $this->age, $this->password, $this->email, $this->addressId, $this->personId);
        $result = $stmt->execute();
        if (!$result) {
            echo "Execute failed: " . $stmt->error;
            return false;
        }

        // Update Volunteer record
        $query = "UPDATE Patient SET PersonID = ? WHERE ID = ?";
        $stmt = $dbConnection->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $dbConnection->error;
            return false;
        }

        $stmt->bind_param("ii", $this->personId, $this->id);
        $result = $stmt->execute();
        if (!$result) {
            echo "Execute failed: " . $stmt->error;
            return false;
        }

        return true;
    }
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

    public function readPatient($dbConnection, $patientId) {
        // Load the volunteer's details based on their ID
        $query = "SELECT Person.ID as PersonID, Person.Name, Person.Age, Person.Password, Person.Email, Person.AddressID, Patient.ID as PatientID
                  FROM Patient 
                  INNER JOIN Person ON Patient.PersonID = Person.ID 
                  WHERE Patient.ID = ?";
        
        $stmt = $dbConnection->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $dbConnection->error;
            return false;
        }

        $stmt->bind_param("i", $patientId);
        $stmt->execute();

        $stmt->bind_result($this->personId, $this->name, $this->age, $this->password, $this->email, $this->addressId, $this->id);

        if ($stmt->fetch()) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($dbConnection, $patientId) {
        if ($patientId === null) {
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
            echo "Patient with ID " . $patientId . " marked as deleted.\n";
        }
        
        return $result;
    }
}
?>