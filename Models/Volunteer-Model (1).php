<?php
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';
require_once 'Person-Model.php';

class Volunteer extends Person {
    protected $id;
    protected $personId;
    protected $job;
    protected $volunteerHours;
    protected $available;
    protected $gender;
    protected $nationalId;
    protected $age;
    protected $skills = [];
    protected $certificates = [];
 


    public function __construct($id = null, $personId = null, $name = "", $gender = "", $nationalId = null, $job = "",
                                 $age = 0, $available = false, $volunteerHours = 0, $skills = [], $certificates = [],
                                 $password = "", $email = "", $addressId = null, $isDeleted = false, $phone = "" ) {
        // Initialize the parent class (Person)
        parent::__construct($id, $name, $age, $password, $email, $addressId, $isDeleted,$phone);
        $this->personId = $personId;
        $this->job = $job;
        $this->volunteerHours = $volunteerHours;
        $this->available = $available;
        $this->gender = $gender;
        $this->nationalId = $nationalId;
        $this->skills = $skills;
        $this->certificates = $certificates;
        $this->dbProxy = new DBProxy($name);

    }

    // Getters and Setters for Volunteer attributes
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

     // Getter and setter for job
     public function getJob() {
        return $this->job;
    }

    public function setJob($job) {
        $this->job = $job;
    }

    // Getter and setter for volunteerHours
    public function getVolunteerHours() {
        return $this->volunteerHours;
    }

    public function setVolunteerHours($volunteerHours) {
        $this->volunteerHours = $volunteerHours;
    }

    // Getter and setter for available
    public function isAvailable() {
        return $this->available;
    }

    public function setAvailable($available) {
        $this->available = $available;
    }

    // Getter and setter for gender
    public function getGender() {
        return $this->gender;
    }

    public function setGender($gender) {
        $this->gender = $gender;
    }

    // Getter and setter for nationalId
    public function getNationalId() {
        return $this->nationalId;
    }

    public function setNationalId($nationalId) {
        $this->nationalId = $nationalId;
    }

    // Getter and setter for age
    public function getAge() {
        return $this->age;
    }

    public function setAge($age) {
        $this->age = $age;
    }

    // Getter and setter for skills
    public function getSkills() {
        return $this->skills;
    }

    public function setSkills(array $skills) {
        $this->skills = $skills;
    }

    // Getter and setter for certificates
    public function getCertificates() {
        return $this->certificates;
    }

    public function setCertificates(array $certificates) {
        $this->certificates = $certificates;
    }

    public function login($email, $enteredPassword) {
        $email = trim($email);
    
        $query = "SELECT Person.ID AS PersonID, Person.Name, Person.Age, Person.Password, Person.Email, Person.AddressID, Volunteer.ID AS VolunteerID, Person.IsDeleted
                  FROM Volunteer 
                  INNER JOIN Person ON Volunteer.PersonID = Person.ID 
                  WHERE Person.Email = ?
                  ORDER BY Person.ID DESC
                  LIMIT 1";
    
        // Prepare the query and handle errors
        $stmt = $this->dbProxy->prepare($query, [$email]);
        $stmt->bind_result($this->personId, $this->name, $this->age, $this->password, $this->email, $this->addressId, $this->id, $this->IsDeleted);

        // Fetch the results and validate the password
        if ($stmt->fetch()) {
            if (($enteredPassword == $this->password) && !$this->IsDeleted) {
                return true;
            }
        }
        // If the query or validation fails, return false
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
            $result = $this->createVolunteer();
        
            if ($result) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function createVolunteer() {

        if ($this->id === null) {
            $personId = $this->createPerson();
            if (!$personId) {
                return false;
            }
            $this->personId = $personId; 
        }

        $query = "INSERT INTO Volunteer (PersonID) VALUES (?)";
        $stmt = $this->dbProxy->prepare($query, [$this->id]);
        
        if ($stmt) {
            $this->id = $this->dbProxy->getInsertId();
            return true;
        }
    
        return false;
    }

    public function updateVolunteer() {
        $personUpdated = $this->updatePerson([$this->name, $this->age, $this->password, $this->email, $this->addressId, $this->personId]);

        if (!$personUpdated) {
            return false;  // Person update failed
        }

        // Update Volunteer record
        $query = "UPDATE Volunteer SET Job = ?, VolunteerHours = ?, Available = ?, Gender = ? WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->job, $this->volunteerHours, $this->available, $this->gender, $this->id]);

        if (!$stmt) {
            return false;
        }

        return true;
    }

    public function readVolunteer($volunteerId) {
        // Load the volunteer's details based on their ID
        $query = "SELECT Person.ID as PersonID, Person.Name, Person.Age, Person.Password, Person.Email, Person.AddressID, Volunteer.ID as VolunteerID, Volunteer.Job, Volunteer.VolunteerHours, Volunteer.Available, Volunteer.Gender
                  FROM Volunteer 
                  INNER JOIN Person ON Volunteer.PersonID = Person.ID 
                  WHERE Volunteer.ID = ?";
    

        $stmt = $this->dbProxy->prepare($query, [$volunteerId]);


        $stmt->bind_result($this->personId, $this->name, $this->age, $this->password, $this->email, $this->addressId, $this->id, $this->job, $this->volunteerHours, $this->available, $this->gender);

        if ($stmt->fetch()) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($volunteerId) {
        if ($volunteerId === null) {
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
        
        $query = "SELECT Person.ID as PersonID, Person.Email, Person.IsDeleted, Volunteer.ID as VolunteerID
                  FROM Volunteer 
                  INNER JOIN Person ON Volunteer.PersonID = Person.ID 
                  WHERE Person.Email = ?
                  AND Person.IsDeleted = 0
                  ORDER BY Person.ID DESC
                  LIMIT 1";
    
       
        $stmt = $this->dbProxy->prepare($query, [$email]);
        if (!$stmt) {
            return false;
        }
    
        $stmt->bind_result($this->personId, $this->email,$this->IsDeleted, $this->id);

        echo $this->personId;
    
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
    public static function getAllVolunteers(): array {
        $dbProxy = new DBProxy('Volunteers');
        $query = "SELECT Person.ID as PersonID, Person.Name, Person.Email, Person.Phone, Volunteer.ID as VolunteerID
                  FROM Volunteer
                  INNER JOIN Person ON Volunteer.PersonID = Person.ID
                  WHERE Person.IsDeleted = 0";
        
        $conn = DBConnection::getInstance()->getConnection();
        $stmt = $conn->prepare($query);
    
        $volunteers = [];
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
    
            while ($row = $result->fetch_assoc()) {
                $volunteer = new Volunteer(
                    id: $row['VolunteerID'],
                    personId: $row['PersonID'],
                    name: $row['Name'],
                    email: $row['Email'],
                    phone: $row['Phone'],
                    isDeleted: false
                );
                $volunteers[] = $volunteer;
            }
    
            $stmt->close();
        }
    
        return $volunteers;
    }
    

}

?>