<?php
require_once 'Donation.php';

class MedicalDonation extends Donation {
    /**
     * @var array A map of MedicalID to its corresponding quantity
     */
    private array $medicalItems = [];

    public function __construct(IDonationMethodStrategy $donationMethod) {
        parent::__construct($donationMethod, DonationType::Medical);
    }

    /**
     *
     * @param Medical $medical The medical item to add
     * @param int $quantity The quantity to add
     */
    public function addToMedicalItems(Medical $medical, int $quantity): void {
        $medicalId = $medical->getId();

        if (isset($this->medicalItems[$medicalId])) {
            $this->medicalItems[$medicalId]['quantity'] += $quantity;
        } else {
            // Add a new medical item
            $this->medicalItems[$medicalId] = [
                'medical' => $medical,
                'quantity' => $quantity,
            ];
        }
    }

    /**
     * Get all medical items as a map.
     * @return array
     */
    public function getMedicalItems(): array {
        return $this->medicalItems;
    }
    public function setMedicalItems(array $medicalItems): void {
        foreach ($medicalItems as $item) {
            if (!is_array($item) || !isset($item['medical']) || !isset($item['quantity'])) {
                throw new Exception("Invalid medical item format. Each item must be an array with 'medical' and 'quantity' keys.");
            }
    
            $medical = $item['medical'];
            $quantity = $item['quantity'];
    
            if (!$medical instanceof Medical) {
                throw new Exception("Invalid medical item. Must be an instance of the Medical class.");
            }
    
            if (!is_int($quantity) || $quantity <= 0) {
                throw new Exception("Invalid quantity. Must be a positive integer.");
            }
    
            $this->medicalItems[] = [
                'medical' => $medical,
                'quantity' => $quantity,
            ];
        }
    }
    
    
    /**
     * Create a new Medical Donation record.
     * @return bool to make sure wether the operation was successfull or not
     */
    public function createMedicalDonation(): bool {
        $conn = DBConnection::getInstance()->getConnection();
    
        try {
            // Start a transaction to ensure atomicity
            $conn->begin_transaction();
    
            foreach ($this->medicalItems as $item) {
                $medical = $item['medical'];
                $quantity = $item['quantity'];
    
                // Ensure medical item is an instance of Medical
                if (!$medical instanceof Medical) {
                    throw new Exception("Invalid medical item. Must be an instance of the Medical class.");
                }
    
                // Step 1: Check if the medical item exists in the Medical table
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
                    // Medical item exists, update quantity in the Medical table
                    $existingRow = $checkMedicalResult->fetch_assoc();
                    $medicalId = (int) $existingRow['ID'];
                    $newQuantity = (int) $existingRow['Quantity'] + $quantity;
    
                    // Update the quantity in the Medical table
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
                    // Medical item does not exist, insert into Medical table
                    $medical->createMedical();
                    $medicalId = $medical->getId(); // Get the ID of the newly inserted medical item
                }
    
                // Close the checkMedicalStmt
                $checkMedicalStmt->close();
    
                // Step 2: Check if the medical item already exists in the DonationMedical table
                $checkDonationMedicalQuery = "SELECT Quantity FROM DonationMedical WHERE MedicalID = ? AND DonationID = ?";
                $checkDonationMedicalStmt = $conn->prepare($checkDonationMedicalQuery);
    
                if (!$checkDonationMedicalStmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
    
                $donationId = $this->getId(); // Get the donation ID from the donation object
                $checkDonationMedicalStmt->bind_param("ii", $medicalId, $donationId);
                $checkDonationMedicalStmt->execute();
                $checkDonationMedicalResult = $checkDonationMedicalStmt->get_result();
    
                if ($checkDonationMedicalResult->num_rows > 0) {
                    // If the medical item already exists in DonationMedical, update the quantity
                    $existingDonationRow = $checkDonationMedicalResult->fetch_assoc();
                    $newDonationQuantity = (int) $existingDonationRow['Quantity'] + $quantity;
    
                    // Update the quantity in the DonationMedical table
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
                    // If the medical item doesn't exist in DonationMedical, insert it
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
    
                // Close the checkDonationMedicalStmt
                $checkDonationMedicalStmt->close();
            }
    
            // Commit all changes
            $conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback in case of an error
            $conn->rollback();
            throw $e;
        }
    }
    
    
    
    

    /**
     * Read a Medical Donation by ID.
     *
     * @param int $donationId The DonationID to fetch
     * @return MedicalDonation|null The MedicalDonation object, or null if not found
     */
    public static function readMedicalDonation(int $donationId): ?MedicalDonation {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "SELECT MedicalID, Quantity FROM DonationMedical WHERE DonationID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $donationId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $medicalDonation = new MedicalDonation(new InKindDonation());
            $medicalDonation->setId($donationId);

            while ($row = $result->fetch_assoc()) {
                $medical = Medical::readMedical($row['MedicalID']); // Fetch Medical object by ID
                $medicalDonation->addToMedicalItems($medical, $row['Quantity']);
            }

            return $medicalDonation;
        }

        $stmt->close();
        return null;
    }

    /**
     * Update a Medical Donation.
     *
     * @return bool Whether the operation was successful
     */
    public function updateMedicalDonation(): bool {
        $conn = DBConnection::getInstance()->getConnection();

        $conn->begin_transaction();
        try {
            foreach ($this->medicalItems as $medicalId => $item) {
                $quantity = $item['quantity'];

                $query = "UPDATE DonationMedical SET Quantity = ? WHERE DonationID = ? AND MedicalID = ?";
                $stmt = $conn->prepare($query);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $donationId = $this->getId();
                $stmt->bind_param("iii", $quantity, $donationId, $medicalId);
                $result = $stmt->execute();
                if (!$result) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                $stmt->close();
            }

            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            throw $e;
        }
    }
    /**
     * Delete a Medical Donation.
     *
     * @return bool Whether the operation was successful
     */
    public function deleteMedicalDonation(): bool {
        $conn = DBConnection::getInstance()->getConnection();
    
        $conn->begin_transaction(); // Start a transaction
        try {
            // Step 1: Soft delete associated records from the DonationMedical table
            $queryUpdateItems = "UPDATE DonationMedical SET IsDeleted = 1 WHERE DonationID = ?";
            $stmtUpdateItems = $conn->prepare($queryUpdateItems);
            if (!$stmtUpdateItems) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
    
            $donationId = $this->getId();
            $stmtUpdateItems->bind_param("i", $donationId);
            $resultItems = $stmtUpdateItems->execute();
            if (!$resultItems) {
                throw new Exception("Execute failed: " . $stmtUpdateItems->error);
            }
            $stmtUpdateItems->close();
    
            // Step 2: Soft delete the donation record itself in the Donation table
            $queryUpdateDonation = "UPDATE Donation SET IsDeleted = 1 WHERE ID = ?";
            $stmtUpdateDonation = $conn->prepare($queryUpdateDonation);
            if (!$stmtUpdateDonation) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
    
            $stmtUpdateDonation->bind_param("i", $donationId);
            $resultDonation = $stmtUpdateDonation->execute();
            if (!$resultDonation) {
                throw new Exception("Execute failed: " . $stmtUpdateDonation->error);
            }
            $stmtUpdateDonation->close();
    
            $conn->commit(); // Commit the transaction
            return true;
    
        } catch (Exception $e) {
            $conn->rollback(); // Rollback if there is an error
            throw $e;
        }
    }
    
}

?>
