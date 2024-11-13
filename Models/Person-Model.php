<?php
abstract class Person {
    protected $id;
    protected $name;
    protected $age;
    protected $password;
    protected $email;
    protected $addressId;
    protected $IsDeleted;

    public function __construct($id = null, $name = "", $age = 0, $password = "", $email = "", $addressId = null,$IsDeleted=0) {
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
    
        // Create associated Person record
        public function createPerson() {
            $conn = DBConnection::getInstance()->getConnection();
    
            if ($this->id !== null) {
                return false;
            }
    
            $query = "INSERT INTO Person (Name, Age, Password, Email, AddressID) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
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
                
            }
            return $result;
        }
    abstract public function login($email, $enteredPassword);
    //abstract public function signup($name, $age, $password, $email, $addressId);
    abstract public function signup($name, $age, $password, $email);

        
}
?>
