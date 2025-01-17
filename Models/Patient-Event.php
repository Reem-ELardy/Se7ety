<?php

require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';

class PatientEvent {
    private int $eventID;
    private int $patientID;
    private int $isDeleted;
    protected $dbProxy;


    public function __construct(int $eventID, int $patientID) {
        $this->dbProxy = new DBProxy('user');
        $this->eventID = $eventID;
        $this->patientID = $patientID;
        $this->isDeleted = 0; 
    }

    public function create(): bool {

        $query = "INSERT INTO PatientEvent (EventID, PatientID, IsDeleted) VALUES (?, ?, 0)";
        $stmt = $this->dbProxy->prepare($query, [$this->eventID, $this->patientID]);
        if (!$stmt) {
            return false;
        }

        return true;
    }


    public function delete(): bool {
        $query = "UPDATE PatientEvent SET IsDeleted = 1 WHERE EventID = ? AND PatientID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->eventID, $this->patientID]);
        if (!$stmt) {
            return false;
        }

        $this-> isDeleted = 1;

        return true;
    }


    public function read(int $eventId, int $patientId): bool {
        $query = "SELECT EventID, PatientID, IsDeleted FROM PatientEvent WHERE EventID = ? AND PatientID = ?";
        $stmt = $this->dbProxy->prepare($query, [$eventId, $patientId]);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_result($this->eventID, $this->patientID, $this->isDeleted);
        if ($stmt->fetch()) {
            return true;
        } else {
            return false; 
        }
    }

}




?>