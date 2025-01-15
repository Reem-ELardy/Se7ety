<?php
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';
require_once 'Person-Model.php';


class Patient extends Person {
    protected $id;
    protected $personId;
    protected $medicalHistory;
    protected $needs;
    protected $nationalId;
    protected $needslist=[];
    private $dbProxy;

    public function __construct($id = null, $personId = null, $name = "", $needs = "", $nationalId = null,
                                 $age = 0,  $medicalHistory = "", $needslist = [],
                                 $password = "", $email = "", $addressId = null, $IsDeleted = false,$phone = "" ) {
        // Initialize the parent class (Person)
        parent::__construct($id, $name, $age, $password, $email, $addressId, $IsDeleted, $phone);
        $this->dbProxy = new DBProxy($name); 
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
        // First, create the associated Person record
        if ($this->id === null) {
            $personId = $this->createPerson();
            if (!$personId) {
                return false;
            }
            $this->personId = $personId; 
        }

        // Create Volunteer record
        $query = "INSERT INTO Patient (PersonID) VALUES (?)";

        $stmt = $this->dbProxy->prepare($query, [$this->id]);

        if ($stmt) {
            $this->id = $this->dbProxy->getInsertId();
            return true;
        }
    
        return false;
    }

    public function updatePatient() {
        $personUpdated = $this->updatePerson([$this->name, $this->age, $this->password, $this->email, $this->addressId, $this->personId]);

        if (!$personUpdated) {
            return false;  // Person update failed
        }

        // Update Volunteer record
        $query = "UPDATE Patient SET PersonID = ? WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [ $this->personId, $this->id]);


        if (!$stmt) {
            return false;
        }
        return true;
    }

    public function login($email, $enteredPassword) {
        $email = trim($email);
        $query = "SELECT Person.ID as PersonID, Person.Name, Person.Age, Person.Password, Person.Email, Person.AddressID, Patient.ID as PatientID
                  FROM Patient 
                  INNER JOIN Person ON Patient.PersonID = Person.ID 
                  WHERE Person.Email = ?
                  ORDER BY Person.ID DESC
                  LIMIT 1";

        $stmt = $this->dbProxy->prepare($query, [$email]);

        $stmt->bind_result($this->personId, $this->name, $this->age, $this->password, $this->email, $this->addressId, $this->id);
        if ($stmt->fetch() && $enteredPassword === $this->password && !$this->IsDeleted) {
            return true;
        }
        return false;
    }

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
            $result = $this->createPatient();
        
            if ($result) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function readPatient($patientId) {

        // Load the volunteer's details based on their ID
        $query = "SELECT Person.ID as PersonID, Person.Name, Person.Age, Person.Password, Person.Email, Person.AddressID, Patient.ID as PatientID
                  FROM Patient 
                  INNER JOIN Person ON Patient.PersonID = Person.ID 
                  WHERE Patient.ID = ?";
        
        $stmt = $this->dbProxy->prepare($query, [$patientId]);

        $stmt->bind_result($this->personId, $this->name, $this->age, $this->password, $this->email, $this->addressId, $this->id);

        if ($stmt->fetch()) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($patientId) {
        if ($patientId === null) {
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
        
        $query = "SELECT Person.ID as PersonID, Person.Email, Patient.ID as PatientID, Person.IsDeleted
                  FROM Patient 
                  INNER JOIN Person ON Patient.PersonID = Person.ID 
                  WHERE Person.Email = ? 
                  AND Person.IsDeleted = 0
                  ORDER BY Person.ID DESC
                  LIMIT 1";
    
        $stmt = $this->dbProxy->prepare($query, [$email]);
    
        if (!$stmt) {
            return false; 
        }
    
        $stmt->bind_result($this->personId, $this->email, $this->id,$this->IsDeleted);
    
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