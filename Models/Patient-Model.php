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
    protected $dbProxy;

    public function __construct($id = null, $personId = null, $name = "", $needs = "", $nationalId = null,
                                 $age = 0,  $medicalHistory = "", $needslist = [],
                                 $password = "", $email = "", $addressId = null, $IsDeleted = false,$phone = "" ) {
      
        parent::__construct($id, $name, $age, $password, $email, $addressId, $IsDeleted, $phone);
        $this->dbProxy = new DBProxy($name); 
        $this->personId = $personId;
        $this->medicalHistory = $medicalHistory;
        $this->needs = $needs;
        $this->needslist = $needslist;
        $this->nationalId = $nationalId;
    }


    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }
    
     public function getMedicalHistory() {
        return $this->medicalHistory;
    }

    public function setMedicalHistory($medicalHistory) {
        $this->medicalHistory = $medicalHistory;
    }


    public function getNeeds() {
        return $this->needs;
    }

    public function setNeeds($needs) {
        $this->needs = $needs;
    }


    // Getter and Setter for needslist
    public function getNeedslist() {
        return $this->needslist;
    }

    public function setNeedslist($needslist) {
        $this->needslist = $needslist;
    }

    public function createPatient() {        

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
            return false;
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

    public function signup($name, $age, $password, $email,$phone) {
        // Input validation (you can expand this to include more robust checks)
        if (empty($name) || empty($age) || empty($password) || empty($email)) {
            return false;
        }

        // Check if the email already exists
        $IsPersonExist = $this->findByEmail($email);
        if ($IsPersonExist) {
            return false;
        } else {
            $this->name = $name;
            $this->age = $age;
            $this->password = $password;
            $this->email = $email;
            $this->phone=$phone;

            $result = $this->createPatient();

            if ($result) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function readPatient($patientId) {
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

    public function createRequest(string $medicalName): bool {
        $medical = new Medical();
        $medicalId = $medical->getMedicalIdByName($medicalName);
    
        if ($medicalId === null) {
            return false;
        }
        $patientNeed = new PatientNeed($medicalId, $this->id);
        $patientNeed->setStatus(NeedStatus::Waiting);
    
        return $patientNeed->createPatientNeed(); 
    }
    
    

    /**
     * Retrieve all PatientNeeds associated with this patient.
     *
     * @return array|null An array of PatientNeeds or null if none are found.
     */
    public function retrieveNeeds(): ?array {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "SELECT MedicalID, Status 
                  FROM PatientNeed 
                  WHERE PatientID = ? AND IsDeleted = 0";
        $stmt = $conn->prepare($query);

        $stmt->bind_param("i", $this->id);
        $stmt->execute();

        $result = $stmt->get_result();

        $needs = [];
        while ($row = $result->fetch_assoc()) {
            $patientNeed = new PatientNeed($row['MedicalID'], $this->id);
            $patientNeed->setStatus(NeedStatus::from($row['Status']));
            $needs[] = $patientNeed;
        }

        $stmt->close();
        $this->setNeedslist($needs);

        return $needs;
    }

    public function getAllPatients(): array {
        $conn = DBConnection::getInstance()->getConnection();
        $query = "SELECT Person.ID as PersonID, Person.Name, Person.Email, Person.Phone, Patient.ID as PatientID
                  FROM Patient
                  INNER JOIN Person ON Patient.PersonID = Person.ID
                  WHERE Person.IsDeleted = 0";
    
        $patients = [];
        $stmt = $conn->prepare($query);
    
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $patient = new Patient(
                    id: $row['PatientID'],
                    personId: $row['PersonID'],
                    name: $row['Name'],
                    email: $row['Email'],
                    phone: $row['Phone']
                );
                $patients[] = $patient;
            }
            $stmt->close();
        }
    
        return $patients;
    }
}
?>