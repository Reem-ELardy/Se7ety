<?php
require_once 'DB-Connection.php';
abstract class Receipt {
    private ?int $id = null;
    private int $donateId;
    private Donate $donate;

    public function __construct(int $donateId, ?int $id = null) {
        $this->donateId = $donateId;
        $this->id = $id;
    }
    
    public function getId(): ?int {
        return $this->id;
    }

    public function getDonateId(): int {
        return $this->donateId;
    }

    public function setDonateId(int $donateId): void {
        $this->donateId = $donateId;
    }

    
    abstract public function generate_receipt();
    abstract public function total_donation();
    
   
    public function createReceipt(): bool {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "INSERT INTO Receipt (DonateID) VALUES (?)";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $this->donateId);
        $result = $stmt->execute();

        if ($result) {
            $this->id = $conn->insert_id;
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        return $result;
    }

    public static function readReceipt(int $id): ?Receipt {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "SELECT * FROM Receipt WHERE ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return new Receipt(
                (int) $row['DonateID'],
                (int) $row['ID']
            );
        }

        $stmt->close();
        return null;
    }
 
    public function updateReceipt(): bool {
        if (!$this->id) {
            throw new Exception("Cannot update a record without an ID.");
        }

        $conn = DBConnection::getInstance()->getConnection();

        $query = "UPDATE Receipt SET DonateID = ? WHERE ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("ii", $this->donateId, $this->id);
        $result = $stmt->execute();

        if (!$result) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        return $result;
    }


    public function deleteReceipt(): bool {
        if (!$this->id) {
            throw new Exception("Cannot delete a record without an ID.");
        }

        $conn = DBConnection::getInstance()->getConnection();

        $query = "DELETE FROM Receipt WHERE ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $this->id);
        $result = $stmt->execute();

        if (!$result) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        return $result;
    }
}

?>