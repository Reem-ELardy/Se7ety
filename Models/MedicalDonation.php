<?php
require_once 'Donation.php';
class MedicalDonation extends Donation {
    private array $medicalItems = [];

    public function __construct(IDonationMethodStrategy $donationMethod) {
        parent::__construct($donationMethod, DonationType::Medical);
    }


    public function addToMedicalItems(Medical $medical, int $quantity): void {
        $this->medicalItems[$medical->getName()] = [
            'medical' => $medical,
            'quantity' => $quantity
        ];
    }


    public function getMedicalItems(): array {
        return $this->medicalItems;
    }

    public function createMedicalDonation(): bool {
        $conn = DBConnection::getInstance()->getConnection();
        
      
        $conn->begin_transaction();
    
        try {

            $query = "INSERT INTO DONATIONMEDICAL (MedicalID, Quantity) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
    
         
            foreach ($this->medicalItems as $item) {
                $medical = $item['medical'];
                $quantity = $item['quantity'];
    
                
                $stmt->bind_param("ii", $medicalId, $quantity);
                $result = $stmt->execute();
                if (!$result) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
            }
    
          
            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollback();
            throw new Exception("Error occurred: " . $e->getMessage());
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }
    

    public static function readMedicalDonation(int $donationId): ?MedicalDonation {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "SELECT * FROM DONATIONMEDICAL WHERE DonationID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $donationId);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
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
        
        $conn->begin_transaction();
    
        try {
            foreach ($this->medicalItems as $item) {
                $medical = $item['medical'];
                $quantity = $item['quantity'];
    
                $queryUpdate = "UPDATE DONATIONMEDICAL SET Quantity = ? WHERE DonationID = ? AND MedicalID = ?";
                $stmtUpdate = $conn->prepare($queryUpdate);
                if (!$stmtUpdate) {
                    throw new Exception("Prepare failed for updating medical item quantity: " . $conn->error);
                }

                $stmtUpdate->bind_param("iii", $quantity, $donationId, $medicalId);
                $result = $stmtUpdate->execute();
                if (!$result) {
                    throw new Exception("Execute failed for updating medical item quantity: " . $stmtUpdate->error);
                }
            }

            $conn->commit();
            return true;
        } catch (Exception $e) {
           
            $conn->rollback();
            throw new Exception("Error occurred: " . $e->getMessage());
        } finally {
           
            if (isset($stmtUpdate)) {
                $stmtUpdate->close();
            }
        }
    }
    
public function deleteMedicalDonation(int $donationId): bool {
    $conn = DBConnection::getInstance()->getConnection();

    $conn->begin_transaction();

    try {
        $queryDeleteItems = "DELETE FROM DONATIONMEDICAL WHERE DonationID = ?";
        $stmtDeleteItems = $conn->prepare($queryDeleteItems);
        if (!$stmtDeleteItems) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmtDeleteItems->bind_param("i", $donationId);
        $stmtDeleteItems->execute();
        $queryDelete = "DELETE FROM DONATIONMEDICAL WHERE DonationID = ?";
        $stmtDelete = $conn->prepare($queryDelete);
        if (!$stmtDelete) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmtDelete->bind_param("i", $donationId);
        $result = $stmtDelete->execute();

        $conn->commit();
        return $result;
    } catch (Exception $e) {
    
        $conn->rollback();
        throw new Exception("Error occurred: " . $e->getMessage());
    } 
        $stmtDeleteItems->close();
        $stmtDelete->close();
    
}

}
?>
