<?php
require_once 'Person-Model.php';
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';

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
        $this->dbProxy = new DBProxy($name); // Initialize DBProxy with a context
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
    public function login($email, $enteredPassword): bool {
        $query = "
            SELECT Person.ID as PersonID, Person.Name, Person.Age, Person.Password, Person.Email, 
                   Person.AddressID, Person.IsDeleted, Admin.Role
            FROM Admin 
            INNER JOIN Person ON Admin.PersonID = Person.ID 
            WHERE Person.Email = ? AND Person.IsDeleted = 0
            LIMIT 1";
    
        $stmt = $this->dbProxy->prepare($query, [$email]);

        if (!$stmt) {
            return false; // Handle query preparation failure
        }

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
    public function signup($name, $age, $password, $email): bool {
        if (empty($name) || empty($age) || empty($password) || empty($email)) {
            return false;
        }

        if ($this->findByEmail($email)) {
            return false; // Email already exists
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
        // Step 1: Insert into the Person table if not already created
        if ($this->id === null) {
            $personId = $this->createPerson(); // Call the Person's create method
            if (!$personId) {
                return false; // Failed to insert into the Person table
            }
            $this->id = $personId; // Save the newly created PersonID
        }

        // Step 2: Check if an Admin record already exists for this PersonID
        $query = "SELECT 1 FROM Admin WHERE PersonID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->id]);
        if ($stmt && $stmt->fetch()) {
            return false; // Admin record already exists
        }

        // Step 3: Insert into the Admin table
        $query = "INSERT INTO Admin (PersonID, Role) VALUES (?, ?)";
        $stmt = $this->dbProxy->prepare($query, [$this->id, $this->role->value]);

        return (bool) $stmt; // Return true if insertion succeeds
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
            SELECT Person.ID as PersonID, Person.Email, Admin.ID as AdminID, Person.IsDeleted, Admin.Role
            FROM Admin 
            INNER JOIN Person ON Admin.PersonID = Person.ID 
            WHERE Person.Email = ? AND Person.IsDeleted = 0
            LIMIT 1";

        $stmt = $this->dbProxy->prepare($query, [$email]);

        if (!$stmt) {
            return false; // Handle query preparation failure
        }

        $stmt->bind_result($this->id, $this->email, $adminId, $this->IsDeleted, $role);

        if ($stmt->fetch()) {
            if ($this->IsDeleted) {
                return false; // Account is deleted
            }

            $this->role = Role::from($role);
            return true;
        }

        return false;
    }

    public static function isValidRole(string $role): bool {
        foreach (Role::cases() as $validRole) {
            if ($validRole->value === $role) {
                return true;
            }
        }
        return false;
    }
}
?>