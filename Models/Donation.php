<?php
enum DonationType: string {
    case Medical = 'Medical';
    case Money = 'Money';
}
class Donation {
    protected IDonationMethodStrategy $donationMethod;
    private ?int $id = null; 
    protected DonationType $donationtype;
    private ?float $cashamount = null; 

    public function __construct(IDonationMethodStrategy $donationMethod, DonationType $donationtype, ?float $cashamount = null) {
        $this->donationMethod = $donationMethod;
        $this->donationtype = $donationtype;
        $this->cashamount = $cashamount; 
        if ($donationtype === DonationType::Medical) {
            if ($cashamount !== null) {
                throw new Exception("Cash amount should not be set for Medical donations.");
            }
        } elseif ($donationtype === DonationType::Money) {
            if ($cashamount === null) {
                throw new Exception("Cash amount must be set for Money donations.");
            }
            // Assign cash amount only for Money donations
            $this->cashamount = $cashamount;
        } else {
            throw new Exception("Invalid donation type."); // Handle unexpected donation types.
        }
        
    }

    public function process(float $amount, int $quantity, string $itemDescription): void {
        $this->donationMethod->processDonation($amount, $quantity, $itemDescription);
    }


    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getCashAmount(): ?float {
        return $this->cashamount;
    }

    public function setCashAmount(?float $cashamount): void {
        if ($this->donationtype === DonationType::Money) {
            $this->cashamount = $cashamount;
        } else {
            throw new Exception("Cash amount can only be set for Money donations.");
        }
    }
    public function getDonationMethod(): ?IDonationMethodStrategy {
        return $this->donationMethod;
    }

    public function getDonationType(): DonationType {
        return $this->donationtype;
    }
    public function setDonationType(DonationType $donationType): void {
        $this->donationtype = $donationType;
    }

    public static function Donate(DonationType $type, string $method, $additionalParams = []): Donation {
        // Handle Medical Donations
        if ($type === DonationType::Medical) {
            $donationMethod = new InKindDonation(); // InKindDonation is for Medical Donations
            $donation = new MedicalDonation($donationMethod);
            
            // Ensure cash amount is never set for Medical donations
            if (isset($additionalParams['cashamount'])) {
                throw new Exception("Cash amount cannot be set for Medical donations.");
            }
        }
        // Handle Money Donations
        elseif ($type === DonationType::Money) {
            // Switch based on the method type for Money donations
            switch ($method) {
                case "Cash":
                    $donationMethod = new CashDonation();
                    break;
                case "Check":
                    // Ensure that all the necessary fields for a CheckDonation are provided
                    if (!isset($additionalParams['checkNumber'], $additionalParams['expirationDate'], $additionalParams['bankName'])) {
                        throw new Exception("Missing parameters for Check donation.");
                    }
                    $donationMethod = new CheckDonation(
                        $additionalParams['checkNumber'],
                        $additionalParams['expirationDate'],
                        $additionalParams['bankName']
                    );
                    break;
                case "EWallet":
                    // Ensure that transactionID is provided for EWalletDonation
                    if (!isset($additionalParams['transactionID'])) {
                        throw new Exception("Missing transaction ID for EWallet donation.");
                    }
                    $donationMethod = new EWalletDonation($additionalParams['transactionID']);
                    break;
                default:
                    throw new Exception("Invalid payment method: $method.");
            }
    
            // Create a new MoneyDonation object
            $donation = new MoneyDonation($donationMethod);
        }
        else {
            throw new Exception("Invalid donation type.");
        }
    
        return $donation;
    }
    

    public function createDonation(int $donateId, ?array $medicalItems = null): bool {
        $conn = DBConnection::getInstance()->getConnection();
    
        try {
            // Start a transaction to ensure atomicity
            $conn->begin_transaction();
    
            $query = "INSERT INTO Donation (DonateID, Type, CashAmount, IsDeleted) VALUES (?, ?, ?, 0)";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            // Set the type to 'Money' and bind the parameters
            $type = $this->donationtype->value; // This should be "Money"
            $cashAmount = $this->cashamount; // Cash amount for money donation
            
            $stmt->bind_param("isd", $donateId, $type, $cashAmount);
            $stmt->execute();
            
            // Check for errors during the execute
            if ($stmt->error) {
                throw new Exception("Execute failed: " . $stmt->error);
            }            
    
            // Fetch the generated ID for this donation
            $this->id = $conn->insert_id;
    
            // Step 2: Handle medical donations if they exist
            if ($this->donationtype === DonationType::Medical && $medicalItems) {
                foreach ($medicalItems as $item) {
                    $medical = $item['medical'];
                    $quantity = $item['quantity'];
                    $checkMedicalQuery = "SELECT ID, Quantity FROM Medical WHERE Name = ? AND Type = ?";
                    $checkMedicalStmt = $conn->prepare($checkMedicalQuery);
    
                    if (!$checkMedicalStmt) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }
    
                    $name = $medical->getName();
                    $type = $medical->getType()->value;
                    $checkMedicalStmt->bind_param("ss", $name, $type);
                    $checkMedicalStmt->execute();
                    $checkMedicalResult = $checkMedicalStmt->get_result();
    
                    if ($checkMedicalResult->num_rows > 0) {
                        // Medical item exists, update the quantity in the Medical table
                        $existingRow = $checkMedicalResult->fetch_assoc();
                        $medicalId = (int) $existingRow['ID'];
                        $newQuantity = (int) $existingRow['Quantity'] + $quantity;
    
                        // Update the quantity of the existing medical item in the Medical table
                        $updateMedicalQuery = "UPDATE Medical SET Quantity = ? WHERE ID = ?";
                        $updateMedicalStmt = $conn->prepare($updateMedicalQuery);
    
                        if (!$updateMedicalStmt) {
                            throw new Exception("Prepare failed: " . $conn->error);
                        }
    
                        $updateMedicalStmt->bind_param("ii", $newQuantity, $medicalId);
                        if (!$updateMedicalStmt->execute()) {
                            throw new Exception("Execute failed for Medical update: " . $updateMedicalStmt->error);
                        }
    
                        $updateMedicalStmt->close();
                    } else {
                        // Medical item does not exist, insert into the Medical table
                        $medical->createMedical();
                        $medicalId = $medical->getId();
                    }
    
                    $checkMedicalStmt->close();
    
                    // Now check the DonationMedical table to see if the medical item already exists
                    $checkDonationMedicalQuery = "SELECT Quantity FROM DonationMedical WHERE MedicalID = ? AND DonationID = ?";
                    $checkDonationMedicalStmt = $conn->prepare($checkDonationMedicalQuery);
    
                    if (!$checkDonationMedicalStmt) {
                        throw new Exception("Prepare failed: " . $conn->error);
                    }
    
                    $donationId = $this->id;
                    $checkDonationMedicalStmt->bind_param("ii", $medicalId, $donationId);
                    $checkDonationMedicalStmt->execute();
                    $checkDonationMedicalResult = $checkDonationMedicalStmt->get_result();
    
                    if ($checkDonationMedicalResult->num_rows > 0) {
                        // DonationMedical entry exists, update quantity
                        $existingDonationRow = $checkDonationMedicalResult->fetch_assoc();
                        $newDonationQuantity = (int) $existingDonationRow['Quantity'] + $quantity;
    
                        // Update the quantity for the existing DonationMedical entry
                        $updateDonationMedicalQuery = "UPDATE DonationMedical SET Quantity = ? WHERE MedicalID = ? AND DonationID = ?";
                        $updateDonationMedicalStmt = $conn->prepare($updateDonationMedicalQuery);
    
                        if (!$updateDonationMedicalStmt) {
                            throw new Exception("Prepare failed: " . $conn->error);
                        }
    
                        $updateDonationMedicalStmt->bind_param("iii", $newDonationQuantity, $medicalId, $donationId);
                        if (!$updateDonationMedicalStmt->execute()) {
                            throw new Exception("Execute failed for DonationMedical update: " . $updateDonationMedicalStmt->error);
                        }
    
                        $updateDonationMedicalStmt->close();
                    } else {
                        // DonationMedical entry does not exist, insert new record
                        $insertDonationMedicalQuery = "INSERT INTO DonationMedical (MedicalID, DonationID, Quantity, IsDeleted) VALUES (?, ?, ?, 0)";
                        $insertDonationMedicalStmt = $conn->prepare($insertDonationMedicalQuery);
    
                        if (!$insertDonationMedicalStmt) {
                            throw new Exception("Prepare failed: " . $conn->error);
                        }
    
                        $insertDonationMedicalStmt->bind_param("iii", $medicalId, $donationId, $quantity);
                        if (!$insertDonationMedicalStmt->execute()) {
                            throw new Exception("Execute failed for DonationMedical insert: " . $insertDonationMedicalStmt->error);
                        }
    
                        $insertDonationMedicalStmt->close();
                    }
    
                    $checkDonationMedicalStmt->close();
                }
            }
    
            // Commit the transaction
            $conn->commit();
            return true;
    
        } catch (Exception $e) {
            // Rollback in case of any error
            $conn->rollback();
            throw $e;
        } finally {
            // Ensure the main statement is always closed
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }
    
    
    public static function readDonation(int $donationId): ?donation {
        $conn = DBConnection::getInstance()->getConnection();
        $query = "SELECT ID, Type, CashAmount FROM Donation WHERE ID = ?";
        $stmt = $conn->prepare($query);
    
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
    
        $stmt->bind_param("i", $donationId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($row = $result->fetch_assoc()) {
            // Determine the donation method based on the donation type
            $donationMethod = self::createDonationMethodFromType($row['Type']);
            
            // Create the donation instance with the correct method and type
            $donation = new self($donationMethod, DonationType::from($row['Type']));
    
            // Set the donation ID
            $donation->setId($row['ID']);
            
            // Set the cash amount for Money donations only
            if ($row['Type'] === DonationType::Money->value) {
                // Set the cash amount for Money donations
                $donation->setCashAmount((float)$row['CashAmount']);
            } else {
                // For Medical donations, do not set cash amount (as it's not applicable)
                $donation->setCashAmount(null);
            }
    
            return $donation;
        } else {
            return null;  // No record found for the given ID
        }
    }
    

 public function updateDonation(): bool {
    $conn = DBConnection::getInstance()->getConnection();
    $query = "UPDATE DONATION SET Type = ?, CashAmount = ? WHERE ID = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $type = $this->donationtype->value;
    $cashamount = $this->cashamount;
    $id = $this->id;

    $stmt->bind_param("sdi", $type, $cashamount, $id);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    return $result;
}
public function deleteDonation(): bool {
    $conn = DBConnection::getInstance()->getConnection();
    $query = "UPDATE DONATION SET IsDeleted = 1 WHERE ID = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $id = $this->id; // ID of the donation to be "soft-deleted"
    $stmt->bind_param("i", $id);

    $result = $stmt->execute();

    if (!$result) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    return $result;
}

private static function createDonationMethodFromType(string $type, ?array $additionalParams = []): IDonationMethodStrategy {
    return match ($type) {
        DonationType::Medical->value => new InKindDonation(), // For Medical donations, use InKindDonation
        DonationType::Money->value => match ($additionalParams['method'] ?? null) {
            'Cash' => new CashDonation(),
            'EWallet' => new EWalletDonation($additionalParams['transactionID'] ?? ''),
            'Check' => new CheckDonation(
                $additionalParams['checkNumber'] ?? '',
                $additionalParams['expirationDate'] ?? new DateTime(),
                $additionalParams['bankName'] ?? ''
            ),
            default => throw new Exception("Unknown money donation method."),
        },
        default => throw new Exception("Unknown donation type."),
    };
}

}

?>