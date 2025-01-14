<?php
require_once 'Admin.php';
require_once 'Medical.php';
require_once 'Patient-need.php';
require_once 'PatientNeedStateFactory.php';


class DonationAdmin extends Admin {
    public function __construct(
        $id = null,
        $name = "",
        $age = 0,
        $password = "",
        $email = "",
        $addressId = null,
        $isDeleted = false
    ) {
        parent::__construct($id, $name, $age, $password, $email, $addressId, $isDeleted, Role::DonationAdmin);
    }

    /**
     * Check if a medical item is available in the database by its ID.
     *
     * @param int $medicalID The ID of the medical item.
     * @return bool True if the medical item is available, false otherwise.
     */
    public function isMedicineAvailable(int $medicalID): bool {
        $medical = new Medical();

        if ($medical->FindMedicalNameByID($medicalID)) {
            $type = $medical->getType();

            if ($type instanceof MedicalType) {
                echo "Medicine Found: Name = {$medical->getName()}, Type = {$type->value}, Quantity = {$medical->getQuantity()}\n";
            } else {
                echo "Medicine Found: Name = {$medical->getName()}, Type = Unknown, Quantity = {$medical->getQuantity()}\n";
            }

            return $medical->getQuantity() > 0;
        } else {
            echo "Medicine with ID '{$medicalID}' not found in the database.\n";
            return false;
        }
    }
    

   /**
 * Process a PatientNeed request based on the requested medicine ID.
 *
 * @param PatientNeed $patientNeed The PatientNeed object to process.
 */
public function processPatientNeed(PatientNeed $patientNeed): void {
    echo "Processing PatientNeed (PatientID: {$patientNeed->getPatientID()})...\n";

    $patientNeed->handleRequest($this);

    $updatedStatus = $patientNeed->getStatus();

    $patientNeed->setState(PatientNeedStateFactory::create($updatedStatus));

    $patientNeed->updatePatientNeed();

    echo "Updated Status: {$patientNeed->getStatus()->value}\n";
    echo "Updated State: " . get_class($patientNeed->getState()) . "\n";
}

    /**
     * Retrieve all PatientNeeds from the database.
     *
     * @return array An array of PatientNeed objects.
     */
    public function retrieveAllPatientNeeds(): array {
        $conn = DBConnection::getInstance()->getConnection();
        $query = "SELECT MedicalID, PatientID, Status FROM PatientNeed WHERE IsDeleted = 0";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            echo "Error preparing statement: " . $conn->error . "\n";
            return [];
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $patientNeeds = [];
        while ($row = $result->fetch_assoc()) {
            try {
  
                $statusEnum = Status::from($row['Status']);
            } catch (ValueError $e) {
                echo "Error: Invalid status value '{$row['Status']}' in the database.\n";
                continue;
            }
            $patientNeed = new PatientNeed($row['MedicalID'], $row['PatientID']);
            $patientNeed->setStatus($statusEnum);

            $patientNeeds[] = $patientNeed;
        }
        $stmt->close();
        return $patientNeeds;
    }

   
}
