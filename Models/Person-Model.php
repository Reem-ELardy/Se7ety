<?php
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';


abstract class Person {
    protected $id;
    protected $name;
    protected $age;
    protected $password;
    protected $email;
    protected $addressId;
    protected $IsDeleted;
    protected $phone;
    private $dbProxy;


    public function __construct($id = null, $name = "", $age = 0, $password = "", $email = "", $addressId = null, $IsDeleted=0, $phone = "") {
        $this->id = $id;
        $this->name = $name;
        $this->age = $age;
        $this->password = $password;
        $this->email = $email;
        $this->addressId = $addressId;
        $this->IsDeleted = $IsDeleted;
        $this->phone = $phone;
        $this->dbProxy = new DBProxy($name);

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
        return $this->IsDeleted;    
    }
    public function getPhone() {
        return $this->phone;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }

    public function updatePerson(array $param) {
        $query = "UPDATE Person SET Name = ?, Age = ?, Password = ?, Email = ?, AddressID = ? , Phone = ? WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, $param);
        if (!$stmt) {
            return false;
        }
        return true;
    }
    
    // Create associated Person record
    public function createPerson() {
        if ($this->id !== null) {
            return false;
        }
    
        $query = "INSERT INTO Person (Name, Age, Password, Email, AddressID, Phone) VALUES (?, ?, ?, ?, ?,?)";
        $stmt = $this->dbProxy->prepare($query, [$this->name, $this->age, $this->password, $this->email, $this->addressId, $this->phone]);
    
        if (!$stmt) {
            return false;
        }
    
        // Assuming DBProxy handles execution and returns the inserted ID
        $this->id = $this->dbProxy->getInsertId();  // Example method to get inserted ID
        return $this->id;
    }
    
    abstract public function login($email, $enteredPassword);
    //abstract public function signup($name, $age, $password, $email, $addressId);
    abstract public function signup($name, $age, $password, $email);
}
?>
