<?php
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';

abstract class Receipt {
    private ?int $id = null;
    private int $donateId;
    private Donate $donate;
    private $dbProxy;


    public function __construct(int $donateId, ?int $id = null) {
        $this->dbProxy = new DBProxy('user');
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
        $query = "INSERT INTO Receipt (DonateID) VALUES (?)";
        $stmt = $this->dbProxy->prepare($query, [$this->donateId]);

        if ($stmt) {
            $this->id = $this->dbProxy->getInsertId();
            return true;
        }

        return false;;
    }

    public function readReceipt(int $id){
        $query = "SELECT * FROM Receipt WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$id]);

        if ($stmt) {
            $stmt->bind_result($this->id, $this->donateId);
            return true;
        }

        return false;
    }

    public function updateReceipt(): bool {
        if (!$this->id) {
            throw new Exception("Cannot update a record without an ID.");
        }
        $query = "UPDATE Receipt SET DonateID = ? WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [ $this->donateId, $this->id]);

        if (!$stmt) {
            return false;
        }

        return true;
    }


    public function deleteReceipt(): bool {
        if (!$this->id) {
            throw new Exception("Cannot delete a record without an ID.");
        }

        $query = "DELETE FROM Receipt WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->id]);

        if (!$stmt) {
            return false;
        }

        return true;
    }
}

?>