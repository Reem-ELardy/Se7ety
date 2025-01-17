<?php
require_once 'Admin.php';
require_once 'Medical.php';
require_once 'Patient-need.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';

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
        // Set the role as DonationAdmin
        parent::__construct($id, $name, $age, $password, $email, $addressId, $isDeleted, Role::DonationAdmin);
        $this->dbProxy = new DBProxy($name); // Initialize the DBProxy
    }

    public function createDonationAdmin(): bool {
        // Use the inherited createAdmin method to insert into Person and Admin tables
        return $this->createAdmin();
    }

    /**
     * Check if a specific medicine is available in the database.
     *
     * @param int $medicalID The ID of the medical item.
     * @return bool True if the medicine is available, false otherwise.
     */
    public function isMedicineAvailable(int $medicalID): bool {
        $query = "SELECT Quantity FROM Medical WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$medicalID]); // Use proxy for database interaction

        if ($stmt) {
            $stmt->bind_result($quantity);
            if ($stmt->fetch()) {
                return $quantity > 0; // Return true if quantity is greater than zero
            }
        }

        return false; // Medicine not found or no quantity available
    }

    /**
     * Process a PatientNeed request and transition its state.
     *
     * @param PatientNeed $patientNeed The PatientNeed object to process.
     */
    public function processPatientNeed(PatientNeed $patientNeed): void {
        // Ensure the state is transitioned properly
        $patientNeed->processPatientNeed($this);

        // Persist changes in the database using the proxy
        $query = "UPDATE PatientNeed SET Status = ? WHERE MedicalID = ? AND PatientID = ? AND IsDeleted = 0";
        $this->dbProxy->prepare($query, [
            $patientNeed->getStatus()->value,
            $patientNeed->getMedicalID(),
            $patientNeed->getPatientID()
        ]);
    }

    /**
     * Retrieve all PatientNeeds from the database.
     *
     * @return array An array of PatientNeed objects.
     */
    public function retrieveAllPatientNeeds(): array {
        $query = "SELECT MedicalID, PatientID, Status FROM PatientNeed WHERE IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($query, []); // Pass query with an empty parameters array


        if (!$stmt) {
            return []; // Return an empty array if the statement couldn't be prepared
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $patientNeeds = [];
        while ($row = $result->fetch_assoc()) {
            try {
                $statusEnum = NeedStatus::from($row['Status']); // Convert status to enum
            } catch (ValueError) {
                continue; // Skip invalid statuses
            }

            $patientNeed = new PatientNeed(
                (int)$row['MedicalID'],
                (int)$row['PatientID'],
                $statusEnum
            );
            $patientNeeds[] = $patientNeed; // Add to the list of needs
        }

        $stmt->close();
        return $patientNeeds;
    }
}
?>
