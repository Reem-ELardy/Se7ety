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

    public function createPatient() {
        $conn = DBConnection::getInstance()->getConnection();
        
        // First, create the associated Person record
        if ($this->id === null) {
            $personCreated = $this->createPerson();
            if (!$personCreated) {
                return false;
            }
        }

        // Create Volunteer record
        $query = "INSERT INTO Patient (PersonID) VALUES (?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $conn->error;
            return false;
        }

        $stmt->bind_param("i", $this->personId);
        $result = $stmt->execute();
        if (!$result) {
            echo "Execute failed: " . $stmt->error;
        } else {
            $this->id = $conn->insert_id;
        }
        return $result;
    }

    public function updatePatient() {
        $conn = DBConnection::getInstance()->getConnection();

        // Update the Person record (related to the volunteer)
        $query = "UPDATE Person SET Name = ?, Age = ?, Password = ?, Email = ?, AddressID = ? WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $conn->error;
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
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $conn->error;
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

    public function login($email, $enteredPassword) {
        $conn = DBConnection::getInstance()->getConnection();

        // Trim whitespace from the email
        $email = trim($email);
    
        // Query to check if the user exists based on their email
        $query = "SELECT Person.ID as PersonID, Person.Name, Person.Age, Person.Password, Person.Email, Person.AddressID, Donor.ID as DonorID
                  FROM Patient 
                  INNER JOIN Person ON Donor.PersonID = Person.ID 
                  WHERE Person.Email = ?";
    
        // Prepare the SQL statement
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
    
        // Bind the email parameter to the query
        $stmt->bind_param("s", $email);
    
        // Execute the statement
        if (!$stmt->execute()) {
            return false;
        }
    
        // Bind the result to the object's properties
        $stmt->bind_result($this->personId, $this->name, $this->age, $this->password, $this->email, $this->addressId, $this->id);
    
        // Fetch the result
        if ($stmt->fetch()) {
            // User with the given email exists
            // Verify the entered password with the stored hashed password
            if ($email === $this->email && $enteredPassword === $this->password) {
                // Password is correct
                return true;
            } else {
                return false;
            }
        } else {
            // User not found
            return false;
        }
    }

    public function signup($name, $age, $password, $email) {
        // Input validation (you can expand this to include more robust checks)
        if (empty($name) || empty($age) || empty($password) || empty($email)) {
            return false;
        }
    
        // Hash the password for security

    
        // Set class properties
        $this->name = $name;
        $this->age = $age;
        $this->password = $password;
        $this->email = $email;
    
        // Use the createPerson method to add the new user to the database
        $result = $this->createPatient();
    
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function createPerson() {
        $conn = DBConnection::getInstance()->getConnection();

        if ($this->id !== null) {
            echo "Error: Cannot create person with an existing ID.";
            return false;
        }

        $query = "INSERT INTO Person (Name, Age, Password, Email, AddressID) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $conn->error;
            return false;
        }

        // Accessing parent class properties with parent::
        $stmt->bind_param("sisss", $this->name, $this->age, $this->password, $this->email, $this->addressId);
        $result = $stmt->execute();
        if (!$result) {
            echo "Execute failed: " . $stmt->error;
        } else {
            // After person is created, set the ID and personId
            $this->id = $conn->insert_id; // Set the ID to the newly created ID
            $this->personId = $this->id; // Set the personId for the Donor
        }
        return $result;
    }

    public function readPatient($patientId) {
        $conn = DBConnection::getInstance()->getConnection();

        // Load the volunteer's details based on their ID
        $query = "SELECT Person.ID as PersonID, Person.Name, Person.Age, Person.Password, Person.Email, Person.AddressID, Patient.ID as PatientID
                  FROM Patient 
                  INNER JOIN Person ON Patient.PersonID = Person.ID 
                  WHERE Patient.ID = ?";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $conn->error;
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

    public function delete($patientId) {
        $conn = DBConnection::getInstance()->getConnection();

        if ($patientId === null) {
            echo "Error: Person ID is not set.";
            return false;
        }
    
        $query = "UPDATE Person SET IsDeleted = true WHERE ID = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            echo "Prepare failed: " . $conn->error;
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