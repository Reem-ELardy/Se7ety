<?php


class PatientEvent {
    private int $eventID;
    private int $patientID;
    private int $isDeleted;


    public function __construct(int $eventID, int $patientID) {
        $this->eventID = $eventID;
        $this->patientID = $patientID;
        $this->isDeleted = 0; 
    }

    public function create(): bool {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "INSERT INTO PatientEvent (EventID, PatientID, IsDeleted) VALUES (?, ?, 0)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
 
        $stmt->bind_param("ii", $this->eventID, $this->patientID);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }


    public function delete(): bool {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "UPDATE PatientEvent SET IsDeleted = 1 WHERE EventID = ? AND PatientID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ii", $this->eventID, $this->patientID);
        $result = $stmt->execute();
        $stmt->close();

        $this-> isDeleted = 1;

        return $result;
    }


    public function read(int $eventId, int $patientId): bool {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "SELECT EventID, PatientID, IsDeleted FROM PatientEvent WHERE EventID = ? AND PatientID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ii", $eventId, $patientId);
        $stmt->execute();

        $stmt->bind_result($this->eventID, $this->patientID, $this->isDeleted);
        if ($stmt->fetch()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false; 
        }
    }

}




?>