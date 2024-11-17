<?php


enum Status: string {
    case Waiting = 'Waiting';
    case Accepted = 'Accepted';
    case Done = 'Done';
}

class PatientNeed{

    private int $MedicalID;
    private int $PatientID;
    private Status $status;


    public function getMedicalID(): int {
        return $this->MedicalID;
    }

    // Setter for MedicalID
    public function setMedicalID(int $MedicalID): void {
        $this->MedicalID = $MedicalID;
    }

    // Getter for PatientID
    public function getPatientID(): int {
        return $this->PatientID;
    }

    // Setter for PatientID
    public function setPatientID(int $PatientID): void {
        $this->PatientID = $PatientID;
    }

    // Getter for Status
    public function getStatus(): Status {
        return $this->status;
    }

    // Setter for Status
    public function setStatus(Status $status): void {
        $this->status = $status;
    }
   

    public function createPatientNeed() {
        $conn = DBConnection::getInstance()->getConnection();

        // Prepare the insert statement
        $query = "INSERT INTO PatientNeed (MedicalID, PatientID, Status) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("iis", $this->MedicalID, $this->PatientID, $this->status);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    // Function to update an existing PatientNeed record
    public function updatePatientNeed(int $medicalID, int $patientID, Status $status) {
        $conn = DBConnection::getInstance()->getConnection();

        // Prepare the update statement
        $query = "UPDATE PatientNeed SET MedicalID = ?, PatientID = ?, Status = ? WHERE MedicalID = ? AND PatientID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }


        $stmt->bind_param("iisii", $medicalID, $patientID, $status, $medicalID, $patientID);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    // Function to read a PatientNeed record from the database
    public function readPatientNeed(int $medicalID, int $patientID): ?array {
        $conn = DBConnection::getInstance()->getConnection();

        // Prepare the select statement
        $query = "SELECT MedicalID, PatientID, Status FROM PatientNeed WHERE MedicalID = ? AND PatientID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return null;
        }

        // Bind parameters and execute the query
        $stmt->bind_param("ii", $medicalID, $patientID);
        $stmt->execute();
        $stmt->bind_result($medicalID, $patientID, $status);
        $stmt->fetch();

        if ($medicalID && $patientID) {
            $result = [
                'MedicalID' => $medicalID,
                'PatientID' => $patientID,
                'Status' => $status
            ];
            $stmt->close();
            return $result;
        }

        $stmt->close();
        return null;
    }

    // Function to delete a PatientNeed record
    public function deletePatientNeed(int $medicalID, int $patientID): bool {
        $conn = DBConnection::getInstance()->getConnection();

        // Prepare the delete statement
        $query = "DELETE FROM PatientNeed WHERE MedicalID = ? AND PatientID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        // Bind parameters and execute the delete
        $stmt->bind_param("ii", $medicalID, $patientID);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function retriveAll(){
        // admin function that will be added in phase 2
    }
}
?>