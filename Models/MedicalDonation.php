<?php
require_once 'Donation.php';
class MedicalDonation extends Donation {
    private array $medicalItems = [];

    public function __construct(IDonationMethodStrategy $donationMethod) {
        // Call the parent constructor, passing the donation method and DonationType::Medical
        parent::__construct($donationMethod, DonationType::Medical);
    }

    // Method to add medical items to the donation
    public function addToMedicalItems(Medical $medical, int $quantity): void {
        $this->medicalItems[$medical->getName()] = [
            'medical' => $medical,
            'quantity' => $quantity
        ];
    }

    // Optional: Get the medical items added to the donation
    public function getMedicalItems(): array {
        return $this->medicalItems;
    }

    // CRUD Operations

    // Create a new Medical Donation record
    public function createMedicalDonation(): bool {
        $conn = DBConnection::getInstance()->getConnection();
        
        // Start a transaction to ensure consistency
        $conn->begin_transaction();
    
        try {
            // Insert the donation itself into the DONATIONMEDICAL table
            $query = "INSERT INTO DONATIONMEDICAL (MedicalID, Quantity) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
    
            // Insert each medical item associated with this donation
            foreach ($this->medicalItems as $item) {
                $medical = $item['medical'];
                $quantity = $item['quantity'];
    
                // Assume $medical->getId() gets the medical ID
                $stmt->bind_param("ii", $medicalId, $quantity);
                $result = $stmt->execute();
                if (!$result) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
            }
    
            // Commit the transaction if everything goes well
            $conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback the transaction if any error occurs
            $conn->rollback();
            throw new Exception("Error occurred: " . $e->getMessage());
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }
    

    // Read a Medical Donation by ID
    public static function readMedicalDonation(int $donationId): ?MedicalDonation {
        $conn = DBConnection::getInstance()->getConnection();

        // Fetch the Medical Donation details
        $query = "SELECT * FROM DONATIONMEDICAL WHERE DonationID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $donationId);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            // Fetch medical items related to this donation
            $queryItems = "SELECT * FROM DONATIONMEDICAL WHERE DonationID = ?";
            $stmtItems = $conn->prepare($queryItems);
            if (!$stmtItems) {
                throw new Exception("Prepare failed: " . $conn->error);
            }

            $stmtItems->bind_param("i", $donationId);
            $stmtItems->execute();
            $resultItems = $stmtItems->get_result();

            $medicalItems = [];
            while ($itemRow = $resultItems->fetch_assoc()) {
                $medical = new Medical(
                    $itemRow['MedicalName'],
                    MedicalType::from($itemRow['Type']), // Assuming `MedicalType::from` method exists
                    new DateTime($itemRow['ExpirationDate']),
                    $itemRow['Quantity']
                );
                $medicalItems[$medical->getName()] = [
                    'medical' => $medical,
                    'quantity' => $itemRow['Quantity']
                ];
            }

            $medicalDonation = new MedicalDonation(new InKindDonation()); // Assuming InKindDonation is your strategy
            $medicalDonation->medicalItems = $medicalItems;
            return $medicalDonation;
        }

        $stmt->close();
        return null;
    }

    public function updateMedicalDonation(int $donationId): bool {
        $conn = DBConnection::getInstance()->getConnection();
        
        // Start a transaction to ensure consistency
        $conn->begin_transaction();
    
        try {
            // Step 1: Update the quantity of medical items in the DONATIONMEDICALItems table
            foreach ($this->medicalItems as $item) {
                $medical = $item['medical'];
                $quantity = $item['quantity'];
    
                // Update the quantity for each medical item
                $queryUpdate = "UPDATE DONATIONMEDICAL SET Quantity = ? WHERE DonationID = ? AND MedicalID = ?";
                $stmtUpdate = $conn->prepare($queryUpdate);
                if (!$stmtUpdate) {
                    throw new Exception("Prepare failed for updating medical item quantity: " . $conn->error);
                }
    
                // Bind the parameters: Quantity, DonationID, MedicalID
                $stmtUpdate->bind_param("iii", $quantity, $donationId, $medicalId);
                $result = $stmtUpdate->execute();
                if (!$result) {
                    throw new Exception("Execute failed for updating medical item quantity: " . $stmtUpdate->error);
                }
            }
    
            // Commit the transaction if everything goes well
            $conn->commit();
            return true;
        } catch (Exception $e) {
            // Rollback if any error occurs
            $conn->rollback();
            throw new Exception("Error occurred: " . $e->getMessage());
        } finally {
            // Close the prepared statement to avoid memory leaks
            if (isset($stmtUpdate)) {
                $stmtUpdate->close();
            }
        }
    }
    
    
// Delete a Medical Donation
public function deleteMedicalDonation(int $donationId): bool {
    $conn = DBConnection::getInstance()->getConnection();

    // Start a transaction to ensure consistency
    $conn->begin_transaction();

    try {
        // Step 1: Delete the medical items for this donation
        $queryDeleteItems = "DELETE FROM DONATIONMEDICAL WHERE DonationID = ?";
        $stmtDeleteItems = $conn->prepare($queryDeleteItems);
        if (!$stmtDeleteItems) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmtDeleteItems->bind_param("i", $donationId);
        $stmtDeleteItems->execute();

        // Step 2: Now delete the donation record itself
        $queryDelete = "DELETE FROM DONATIONMEDICAL WHERE DonationID = ?";
        $stmtDelete = $conn->prepare($queryDelete);
        if (!$stmtDelete) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmtDelete->bind_param("i", $donationId);
        $result = $stmtDelete->execute();

        // Commit the transaction if everything goes well
        $conn->commit();
        return $result;
    } catch (Exception $e) {
        // Rollback if any error occurs
        $conn->rollback();
        throw new Exception("Error occurred: " . $e->getMessage());
    } 
        $stmtDeleteItems->close();
        $stmtDelete->close();
    
}

}
?>
