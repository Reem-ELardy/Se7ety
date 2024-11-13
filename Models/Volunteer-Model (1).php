<?php
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
                                 $password = "", $email = "", $addressId = null, $isDeleted = false) {
        // Initialize the parent class (Person)
        parent::__construct($id, $name, $age, $password, $email, $addressId, $isDeleted);
        $this->personId = $personId;
        $this->job = $job;
        $this->volunteerHours = $volunteerHours;
        $this->available = $available;
        $this->gender = $gender;
        $this->nationalId = $nationalId;
        $this->skills = $skills;
        $this->certificates = $certificates;
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

    public function createVolunteer($dbConnection) {
        // First, create the associated Person record
        if ($this->id === null) {
            $personCreated = $this->createPerson($dbConnection);
            if (!$personCreated) {
                return false;
            }
        }

        // Create Volunteer record
        $query = "INSERT INTO Volunteer (PersonID, Job, VolunteerHours, Available, Gender) VALUES (?, ?, ?, ?, ?)";
        $stmt = $dbConnection->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $dbConnection->error;
            return false;
        }

        $stmt->bind_param("isiss", $this->personId, $this->job, $this->volunteerHours, $this->available, $this->gender);
        $result = $stmt->execute();
        if (!$result) {
            echo "Execute failed: " . $stmt->error;
        } else {
            $this->id = $dbConnection->insert_id;
        }
        return $result;
    }

    public function updateVolunteer($dbConnection) {
        // Update the Person record (related to the volunteer)
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

        // Update Volunteer record
        $query = "UPDATE Volunteer SET Job = ?, VolunteerHours = ?, Available = ?, Gender = ? WHERE ID = ?";
        $stmt = $dbConnection->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $dbConnection->error;
            return false;
        }

        $stmt->bind_param("sisii", $this->job, $this->volunteerHours, $this->available, $this->gender, $this->id);
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

    public function readVolunteer($dbConnection, $volunteerId) {
        // Load the volunteer's details based on their ID
        $query = "SELECT Person.ID as PersonID, Person.Name, Person.Age, Person.Password, Person.Email, Person.AddressID, Volunteer.ID as VolunteerID, Volunteer.Job, Volunteer.VolunteerHours, Volunteer.Available, Volunteer.Gender
                  FROM Volunteer 
                  INNER JOIN Person ON Volunteer.PersonID = Person.ID 
                  WHERE Volunteer.ID = ?";
        
        $stmt = $dbConnection->prepare($query);
        if (!$stmt) {
            echo "Prepare failed: " . $dbConnection->error;
            return false;
        }

        $stmt->bind_param("i", $volunteerId);
        $stmt->execute();

        $stmt->bind_result($this->personId, $this->name, $this->age, $this->password, $this->email, $this->addressId, $this->id, $this->job, $this->volunteerHours, $this->available, $this->gender);

        if ($stmt->fetch()) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($dbConnection, $volunteerId) {
        if ($volunteerId === null) {
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
            echo "Volunteer with ID " . $volunteerId . " marked as deleted.\n";
        }

        return $result;
    }
}
?>