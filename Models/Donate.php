<?php

require_once "Donation.php";

class Donate {
    private int $Donate_ID;
    private int $DonorID;
    private string $Date;
    private string $Time;
    private bool $IsDeleted;
    private array $Donation_Details = []; 


    public function __construct(int $DonorID, string $Date, string $Time) {
        $this->DonorID = $DonorID;
        $this->Date = $Date;
        $this->Time = $Time;
        $this->IsDeleted = false; 
    }

    public function addToList(Donation $donation): void {
        $this->Donation_Details[] = $donation;
    }

    public function getList(): array {
        return $this->Donation_Details;
    }

    public function generateReceipt(): void {
        echo "Donation Receipt\n";
        echo "Donate ID: " . $this->Donate_ID . "\n";
        echo "Donor ID: " . $this->DonorID . "\n";
        echo "Date: " . $this->Date . "\n";
        echo "Time: " . $this->Time . "\n";
        echo "Donations:\n";
        foreach ($this->Donation_Details as $donation) {
            echo "- Donation ID: " . $donation->getDonationID() . " | Details: " . $donation->getDonationDetails() . "\n";
        }
    }

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

    // CRUD Operations

    public function createDonate(): bool {
        $conn = DBConnection::getInstance()->getConnection();
        $query = "INSERT INTO Donate (DonorID, Date, Time, IsDeleted) VALUES (?, ?, ?, 0)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("issi", $this->DonorID, $this->Date, $this->Time, $this->IsDeleted);
        return $stmt->execute();
    }

    public function readDonate(int $Donate_ID): bool {
        $conn = DBConnection::getInstance()->getConnection();
        $query = "SELECT * FROM Donate WHERE ID = ? AND IsDeleted = 0";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $Donate_ID);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $donorID, $date, $time, $isDeleted);
            $stmt->fetch();
            $this->Donate_ID = $id;
            $this->DonorID = $donorID;
            $this->Date = $date;
            $this->Time = $time;
            $this->IsDeleted = $isDeleted;
            return true;
        }

        return false;
    }

    public function updateDonate(): bool {
        $conn = DBConnection::getInstance()->getConnection();
        $query = "UPDATE Donate SET DonorID = ?, Date = ?, Time = ?, IsDeleted = ? WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("issii", $this->DonorID, $this->Date, $this->Time, $this->IsDeleted, $this->Donate_ID);
        return $stmt->execute();
    }

    // Soft delete Donate record (set IsDeleted to true)
    public function deleteDonate(): bool {
        $conn = DBConnection::getInstance()->getConnection();
        $query = "UPDATE Donate SET IsDeleted = 1 WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $this->Donate_ID);
        return $stmt->execute();
    }
}

?>