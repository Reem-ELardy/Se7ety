<?php
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';

class Skills {
    private int $id;
    private string $name;
    private $dbProxy;

    public function __construct(int $id = 0, string $name = '') {
        $this->dbProxy = new DBProxy('user');
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function createSkill() {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "INSERT INTO Skills (Name) VALUES (?)";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $this->name);

        $result = $stmt->execute();
        if ($result) {
            $this->id = $conn->insert_id;
        }

        return $result;
    }

    public function getSkillByID($id) {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "SELECT * FROM Skills WHERE ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }

    public function updateSkill($name) {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "UPDATE Skills SET Name = ? WHERE ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $name, $this->id);

        return $stmt->execute();
    }

    public function deleteSkill($id) {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "DELETE FROM Skills WHERE ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

    public function getAllSkills() {
        $query = 'SELECT * FROM Skills WHERE IsDeleted = 0'; 
        
        $stmt = $this->dbProxy->prepare($query, []);
        
        $result = $stmt->get_result();
    
        if (!$result || $result->num_rows === 0) {
            return false; 
        }
    
        $skillsList = [];
    
        while ($row = $result->fetch_assoc()) {
            $skillsList[] = [
                'id' => $row['ID'],   // Skill ID
                'name' => $row['Name'] // Skill Name
            ];
        }    
        return $skillsList;
    }
    
}

?>