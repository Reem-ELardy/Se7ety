<?php
require_once 'Person-Model.php';

// Define the Role enum
enum Role: string {
    case DonationAdmin = 'DonationAdmin';
    case PaymentAdmin = 'PaymentAdmin';
}

class Admin extends Person {

    protected Role $role;


    public function __construct(
        $id = null,
        $name = "",
        $age = 0,
        $password = "",
        $email = "",
        $addressId = null,
        $isDeleted = false,
        Role $role = Role::DonationAdmin // Default role
    ) {
        parent::__construct($id, $name, $age, $password, $email, $addressId, $isDeleted);
        $this->role = $role;
    }

    // Getter for Role
    public function getRole(): Role {
        return $this->role;
    }

    // Setter for Role
    public function setRole(Role $role): void {
        $this->role = $role;
    }

    // === Implementing Abstract Methods from Person ===

    /**
     * Login an admin by email and password.
     *
     * @param string $email
     * @param string $enteredPassword
     * @return bool True if login is successful, false otherwise.
     */
    public function login($email, $enteredPassword) {
        $query = "SELECT Person.ID as PersonID, Person.Name, Person.Age, Person.Password, Person.Email, Person.AddressID, Person.IsDeleted, Admin.Role
                  FROM Admin 
                  INNER JOIN Person ON Admin.PersonID = Person.ID 
                  WHERE Person.Email = ? AND Person.IsDeleted = 0
                  LIMIT 1";
    
        $stmt = $this->dbProxy->prepare($query, [$email]);
    
        $stmt->bind_result($this->id, $this->name, $this->age, $this->password, $this->email, $this->addressId, $this->IsDeleted, $role);
    
        if ($stmt->fetch() && $enteredPassword === $this->password && !$this->IsDeleted) {
            $this->role = Role::from($role); 
            return true;
        }
    
        return false;
    }
    

    /**
     * Signup a new admin.
     *
     * @param string $name
     * @param int $age
     * @param string $password
     * @param string $email
     * @return bool True if signup is successful, false otherwise.
     */
    public function signup($name, $age, $password, $email) {
        if (empty($name) || empty($age) || empty($password) || empty($email)) {
            echo "All fields are required for signup.\n";
            return false;
        }
    
        $isPersonExist = $this->findByEmail($email);
        if ($isPersonExist) {
            echo "An account with this email already exists.\n";
            return false;
        }
    
        // Set class properties
        $this->name = $name;
        $this->age = $age;
        $this->password = $password;
        $this->email = $email;
        return $this->createAdmin();
    }
    
    /**
     * Create a new admin record in the database.
     *
     * @return bool True if creation is successful, false otherwise.
     */
    public function createAdmin(): bool {
        if ($this->id === null) {
            $personId = $this->createPerson();
            if (!$personId) {
                echo "Error: Failed to create associated Person record.\n";
                return false;
            }
            $this->id = $personId;
        }
        $query = "SELECT 1 FROM Admin WHERE PersonID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->id]);
        if ($stmt && $stmt->fetch()) {
            echo "Error: Admin record already exists for PersonID {$this->id}.\n";
            return false;
        }
        $query = "INSERT INTO Admin (PersonID, Role) VALUES (?, ?)";
        $stmt = $this->dbProxy->prepare($query, [$this->id, $this->role->value]);

        if ($stmt) {
            echo "Admin record created successfully for PersonID {$this->id} with Role '{$this->role->value}'.\n";
            return true;
        }

        echo "Error: Failed to execute Admin creation query.\n";
        return false;
    }

    /**
     * Find an admin by email.
     *
     * @param string $email
     * @return bool True if found, false otherwise.
     */
    public function findByEmail(string $email): bool {
        $email = trim($email);

        $query = "
            SELECT 
                Person.ID as PersonID, 
                Person.Email, 
                Admin.ID as AdminID, 
                Person.IsDeleted, 
                Admin.Role
            FROM Admin 
            INNER JOIN Person ON Admin.PersonID = Person.ID 
            WHERE Person.Email = ? AND Person.IsDeleted = 0
            LIMIT 1";

        $stmt = $this->dbProxy->prepare($query, [$email]);

        if (!$stmt) {
            echo "Error preparing findByEmail query.\n";
            return false;
        }

        $stmt->bind_result($this->id, $this->email, $adminId, $this->IsDeleted, $role);

        if ($stmt->fetch()) {
            if ($this->IsDeleted) {
                echo "This account is marked as deleted.\n";
                return false;
            }

            $this->role = Role::from($role);
            return true;
        }

        return false;
    }

    public static function isValidRole(string $role): bool {
        foreach (self::$role::cases() as $validRole) {
            if ($validRole->value === $role) {
                return true;
            }
        }
        return false;
    }
}
?>
