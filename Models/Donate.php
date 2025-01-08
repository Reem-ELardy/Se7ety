<?php
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';
require_once "Donation.php";


class Donate {
    private int $Donate_ID;
    private int $DonorID;
    private string $Date;
    private string $Time;
    private $IsDeleted;
    private array $Donation_Details = []; 
    private $dbProxy;

    public function __construct(int $DonorID, int $donateID = 0, string $Date = '', string $Time = '', $isDeleted = false) {
        $this->DonorID = $DonorID;
        $this->Donate_ID = $donateID;
        $this->Date = $Date ?: date('Y-m-d');
        $this->Time = $Time ?: date('H:i:s');
        $this->IsDeleted = $isDeleted; 
        $this->dbProxy = new DBProxy('user');
    }

    // Setters and Getters
    public function setDonateID(int $Donate_ID): void {
        $this->Donate_ID = $Donate_ID;
    }

    public function getDonateID(): int {
        return $this->Donate_ID;
    }

    public function setDonorID(int $DonorID): void {
        $this->DonorID = $DonorID;
    }

    public function getDonorID(): int {
        return $this->DonorID;
    }

    public function setDate(string $Date): void {
        $this->Date = $Date;
    }

    public function getDate(): string {
        return $this->Date;
    }

    public function setTime(string $Time): void {
        $this->Time = $Time;
    }

    public function getTime(): string {
        return $this->Time;
    }

    public function setIsDeleted(bool $IsDeleted): void {
        $this->IsDeleted = $IsDeleted;
    }

    public function getIsDeleted(): bool {
        return $this->IsDeleted;
    }

    public function addDonation(Donation $donation): void {
        $this->Donation_Details[] = $donation;
    }

    public function getDonationDetails(): array {
        return $this->Donation_Details;
    }

    // CRUD Operations

    public function createDonate(): bool {
        $query = "INSERT INTO Donate (DonorID, Date, Time, IsDeleted) VALUES (?, ?, ?, 0)";
        $stmt = $this->dbProxy->prepare($query, [$this->DonorID, $this->Date, $this->Time]);

        if($stmt){
            $this->Donate_ID = $this->dbProxy->getInsertId();
            return true;
        }

        return false;
    }

    public function readDonate(int $Donate_ID): bool {
        $query = "SELECT * FROM Donate WHERE ID = ? AND IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($query, [$Donate_ID]);
    
        if ($stmt) {
            $stmt->bind_result($this->Donate_ID, $this->DonorID, $this->Date, $this->Time, $this->IsDeleted);
            $stmt->fetch();
            return true;
        }
        return false;
    }
    

    public function updateDonate(): bool {
        $query = "UPDATE Donate SET DonorID = ?, Date = ?, Time = ?, IsDeleted = ? WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->DonorID, $this->Date, $this->Time, $this->IsDeleted, $this->Donate_ID]);

        if($stmt){
            return true;
        }
        return false;
    }

    public function deleteDonate(): bool {
        $query = "UPDATE Donate SET IsDeleted = 1 WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->Donate_ID]);

        if($stmt){
            return true;
        }

        return false;
    }

    public function readUserDonates(int $DonorID) : array {
        $query = "SELECT * FROM Donate WHERE DonorID = ? AND IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($query, [$DonorID]);
    
        if ($stmt) {
            $stmt->store_result();
            $stmt->bind_result($Donate_ID, $DonorID, $Date, $Time, $IsDeleted);
    
            $donates = [];
            while ($stmt->fetch()) {
                $donate = new Donate($DonorID, (int) $Donate_ID, $Date, $Time, $IsDeleted);
                $donate->setDonateID($Donate_ID);
                $donates[] = $donate;
            }
            return $donates;
        }
        return [];
    }

    public function getAmount(): float {
        $totalAmount = 0.0;
        foreach ($this->Donation_Details as $donation) {
            $totalAmount += $donation->getDonationAmount();
        }
        return $totalAmount;
    }

    public function generateReceipt(): void {
        echo "Donation Receipt\n";
        echo "Donate ID: " . $this->Donate_ID . "\n";
        echo "Donor ID: " . $this->DonorID . "\n";
        echo "Date: " . $this->Date . "\n";
        echo "Time: " . $this->Time . "\n";
        echo "Donations:\n";
    
        foreach ($this->Donation_Details as $donation) {
            if ($donation instanceof MoneyDonation) {
                echo "- Donation ID: " . $donation->getId() . " | Type: ".$donation->getDonationType()  . $donation->getCashAmount() . "\n";
            } elseif ($donation instanceof MedicalDonation) {
                echo "- Donation ID: " . $donation->getId() . " | Type: Medical\n";
                foreach ($donation->getMedicalItems() as $item) {
                    $medical = $item['medical'];
                    $quantity = $item['quantity'];
                    echo "    * Medical Item: " . $medical->getName() . " | Quantity: " . $quantity . " | Expiration Date: " . $medical->getExpirationDate()->format('Y-m-d') . "\n";
                }
            }
        }
    }
    
}

?>