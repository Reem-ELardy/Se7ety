<?php
require_once 'InKindDonation.php';
// Enum for Medical Types
enum MedicalType: string {
    case MEDICINE = 'Medicine';
    case TOOL = 'Tool';
}

class Medical {
    private ?int $id = null;
    private DateTime $expirationDate;
    private string $name;
    private MedicalType $type;
    private int $quantity; 

    public function __construct(string $name, MedicalType $type, DateTime $expirationDate, int $quantity) {
        $this->name = $name;
        $this->type = $type;
        $this->expirationDate = $expirationDate;
        $this->quantity = $quantity; // Initialize Quantity
    }

    // Getter methods
    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getType(): MedicalType {
        return $this->type;
    }

    public function getExpirationDate(): DateTime {
        return $this->expirationDate;
    }

    public function getQuantity(): int {
        return $this->quantity;
    }

    public function isExpired(): bool {
        return $this->expirationDate < new DateTime();
    }

    // Setter for Quantity (if needed)
    public function setQuantity(int $quantity): void {
        $this->quantity = $quantity;
    }

    // CRUD Operations

    // Create a new Medical record
    public function createMedical(): bool {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "INSERT INTO Medical (Name, Type, ExpirationDate, Quantity) VALUES (?, ?, ?, ?)"; 
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $name = $this->name;
        $type = $this->type->value;
        $expirationDate = $this->expirationDate->format('Y-m-d');
        $quantity = $this->quantity; 

        $stmt->bind_param("sssi", $name, $type, $expirationDate, $quantity); 
        $result = $stmt->execute();

        if ($result) {
            $this->id = $conn->insert_id;
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        return $result;
    }

    // Read a Medical record by ID
    public static function readMedical(int $id): ?Medical {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "SELECT * FROM Medical WHERE ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return new Medical(
                $row['Name'],
                MedicalType::from($row['Type']),
                new DateTime($row['ExpirationDate']),
                (int)$row['Quantity'] // Read Quantity from DB
            );
        }

        $stmt->close();
        return null; // No record found
    }

    // Update a Medical record
    public function updateMedical(): bool {
        if (!$this->id) {
            throw new Exception("Cannot update a record without an ID.");
        }

        $conn = DBConnection::getInstance()->getConnection();

        $query = "UPDATE Medical SET Name = ?, Type = ?, ExpirationDate = ?, Quantity = ? WHERE ID = ?"; // Added Quantity field
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $name = $this->name;
        $type = $this->type->value;
        $expirationDate = $this->expirationDate->format('Y-m-d');
        $quantity = $this->quantity; // Get quantity
        $id = $this->id;

        $stmt->bind_param("sssii", $name, $type, $expirationDate, $quantity, $id); // Bind Quantity
        $result = $stmt->execute();

        if (!$result) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        return $result;
    }

    // Delete a Medical record
    public function deleteMedical(): bool {
        if (!$this->id) {
            throw new Exception("Cannot delete a record without an ID.");
        }

        $conn = DBConnection::getInstance()->getConnection();

        $query = "DELETE FROM Medical WHERE ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $id = $this->id;

        $stmt->bind_param("i", $id);
        $result = $stmt->execute();

        if (!$result) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        return $result;
    }
}
?>