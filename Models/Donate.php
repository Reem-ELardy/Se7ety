<?php

require_once "Donation.php";

class Donate {
    private int $Donate_ID;
    private int $DonorID;
    private string $Date;
    private string $Time;
    private bool $IsDeleted;
    private array $Donation_Details = []; // Array to store Donation objects

    /**
     * Constructor to initialize a Donate object.
     * 
     * @param int $DonorID The ID of the donor.
     * @param string $Date The date of the donation.
     * @param string $Time The time of the donation.
     */
    public function __construct(int $DonorID, string $Date, string $Time) {
        $this->DonorID = $DonorID;
        $this->Date = $Date;
        $this->Time = $Time;
        $this->IsDeleted = false; // Default value for new donations
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
    public function createDonate(array $donations, ?array $medicalItems = null): bool {
        $conn = DBConnection::getInstance()->getConnection();
    
        try {
            // Start a transaction
            $conn->begin_transaction();
    
            // Step 1: Insert into the Donate table
            $query = "INSERT INTO Donate (DonorID, Date, Time, IsDeleted) VALUES (?, ?, ?, 0)";
            $stmt = $conn->prepare($query);
    
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
    
            $stmt->bind_param("iss", $this->DonorID, $this->Date, $this->Time);
            $stmt->execute();
    
            // Get the auto-generated Donate_ID
            $this->Donate_ID = $conn->insert_id;
    
            // Step 2: Handle Donations
            foreach ($donations as $donation) {
                if ($donation instanceof MedicalDonation && $medicalItems) {
                    // Set medical items for MedicalDonation
                    $donation->setMedicalItems($medicalItems);
                }
    
                // Create the donation (pass the required arguments)
                $donation->createDonation($this->Donate_ID, $medicalItems);
            }
    
            // Commit the transaction
            $conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback in case of an error
            $conn->rollback();
            throw new Exception("Transaction failed: " . $e->getMessage());
        } finally {
            // Ensure the statement is closed
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }
    

    public function readDonate(int $Donate_ID): bool {
        $conn = DBConnection::getInstance()->getConnection();
        $query = "SELECT * FROM Donate WHERE ID = ? AND IsDeleted = 0";
        $stmt = $conn->prepare($query);
    
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
    
        $stmt->bind_param("i", $Donate_ID);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($row = $result->fetch_assoc()) {
            $this->Donate_ID = (int) $row['ID'];
            $this->DonorID = (int) $row['DonorID'];
            $this->Date = $row['Date'];
            $this->Time = $row['Time'];
            $this->IsDeleted = (bool) $row['IsDeleted'];
    
            // Fetch associated donations
            $this->fetchDonations();
            return true;
        }
    
        return false;
    }
    

    public function updateDonate(): bool {
        $conn = DBConnection::getInstance()->getConnection();
        $query = "UPDATE Donate SET DonorID = ?, Date = ?, Time = ?, IsDeleted = ? WHERE ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("issii", $this->DonorID, $this->Date, $this->Time, $this->IsDeleted, $this->Donate_ID);
        return $stmt->execute();
    }

    public function deleteDonate(): bool {
        $conn = DBConnection::getInstance()->getConnection();
        $query = "UPDATE Donate SET IsDeleted = 1 WHERE ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $this->Donate_ID);
        return $stmt->execute();
    }

    private function fetchDonations(): void {
        $conn = DBConnection::getInstance()->getConnection();
    
        // Fetch all donations linked to the current Donate_ID
        $query = "SELECT * FROM Donation WHERE DonateID = ? AND IsDeleted = 0";
        $stmt = $conn->prepare($query);
    
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
    
        $stmt->bind_param("i", $this->Donate_ID);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Iterate through each donation record
        while ($row = $result->fetch_assoc()) {
            $donationID = (int) $row['ID'];
            $type = $row['Type'];
            $cashAmount = $row['CashAmount'] ?? 0.0;
    
            if ($type === 'Cash') {
                // Handle Cash Donation
                $donation = new MoneyDonation(new CashDonation(), $cashAmount);
            } elseif ($type === 'Medical') {
                // Handle Medical Donation
                $medicalQuery = "SELECT MedicalID, Quantity FROM DonationMedical WHERE DonationID = ? AND IsDeleted = 0";
                $medicalStmt = $conn->prepare($medicalQuery);
    
                if (!$medicalStmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
    
                $medicalStmt->bind_param("i", $donationID);
                $medicalStmt->execute();
                $medicalResult = $medicalStmt->get_result();
    
                $medicalItems = [];
                while ($medicalRow = $medicalResult->fetch_assoc()) {
                    $medicalID = (int) $medicalRow['MedicalID'];
                    $quantity = (int) $medicalRow['Quantity'];
    
                    // Fetch the Medical object from the Medical table
                    $medical = Medical::readMedical($medicalID);
                    if (!$medical) {
                        throw new Exception("Failed to fetch Medical item with ID: $medicalID");
                    }
    
                    // Add the medical item in the expected format
                    $medicalItems[] = [
                        'medical' => $medical,
                        'quantity' => $quantity,
                    ];
                }
    
                // Create a MedicalDonation object
                $donation = new MedicalDonation(new InKindDonation());
                $donation->setId($donationID);
                $donation->setMedicalItems($medicalItems);
    
                $medicalStmt->close();
            } else {
                // Skip unknown types
                continue;
            }
    
            // Set the ID and add the donation to the details array
            $donation->setId($donationID);
            $this->addDonation($donation);
        }
    
        // Close the main statement
        $stmt->close();
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
