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

    // Setter for Quantity
    public function setQuantity(int $quantity): void {
        $this->quantity = $quantity;
    }

    // CRUD Operations

    // Create a new Medical record
    public function createMedical(): bool {
        $conn = DBConnection::getInstance()->getConnection();
    
        // Step 1: Check if the medical item already exists in the database
        $queryCheck = "SELECT ID, Quantity FROM Medical WHERE Name = ? AND Type = ? AND ExpirationDate = ?";
        $stmtCheck = $conn->prepare($queryCheck);
    
        if (!$stmtCheck) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
    
        $name = $this->name;
        $type = $this->type->value;
        $expirationDate = $this->expirationDate->format('Y-m-d');
    
        $stmtCheck->bind_param("sss", $name, $type, $expirationDate);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();
    
        if ($row = $result->fetch_assoc()) {
            // If the medical item exists, update the quantity by adding the new quantity
            $existingId = $row['ID'];
            $existingQuantity = $row['Quantity'];
    
            // Update the existing medical item's quantity by adding the new quantity
            $queryUpdate = "UPDATE Medical SET Quantity = ? WHERE ID = ?";
            $stmtUpdate = $conn->prepare($queryUpdate);
    
            if (!$stmtUpdate) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
    
            // Calculate the new quantity by adding the current quantity to the existing one
            $newQuantity = $existingQuantity + $this->quantity;
    
            $stmtUpdate->bind_param("ii", $newQuantity, $existingId);
            $resultUpdate = $stmtUpdate->execute();
    
            $stmtUpdate->close();
    
            if ($resultUpdate) {
                // Update the object with the ID of the existing record
                $this->id = $existingId;
                return true; // Return true if the update was successful
            } else {
                throw new Exception("Failed to update the existing medical item.");
            }
        } else {
            // Step 2: If no existing record, insert a new one
            $queryInsert = "INSERT INTO Medical (Name, Type, ExpirationDate, Quantity) VALUES (?, ?, ?, ?)";
            $stmtInsert = $conn->prepare($queryInsert);
    
            if (!$stmtInsert) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
    
            // Insert the new medical item
            $quantity = $this->quantity;
            $stmtInsert->bind_param("sssi", $name, $type, $expirationDate, $quantity);
            $resultInsert = $stmtInsert->execute();
    
            if ($resultInsert) {
                // Update the object with the new ID from the insertion
                $this->id = $conn->insert_id;
                $stmtInsert->close();
                return true; // Return true if the insertion was successful
            } else {
                throw new Exception("Failed to insert the new medical item.");
            }
        }
    
        // Ensure that we close the check statement after all logic is finished
        $stmtCheck->close();
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

        $query = "UPDATE Medical SET Name = ?, Type = ?, ExpirationDate = ?, Quantity = ? WHERE ID = ?"; 
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $name = $this->name;
        $type = $this->type->value;
        $expirationDate = $this->expirationDate->format('Y-m-d');
        $quantity = $this->quantity;
        $id = $this->id;

        $stmt->bind_param("sssii", $name, $type, $expirationDate, $quantity, $id);
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
    
        // Update the IsDeleted flag instead of deleting the record
        $query = "UPDATE Medical SET IsDeleted = 1 WHERE ID = ?";
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
    

    // OPTIONAL: Convert to Array (useful for APIs or JSON)
    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'expirationDate' => $this->expirationDate->format('Y-m-d'),
            'quantity' => $this->quantity,
        ];
    }
}

?>