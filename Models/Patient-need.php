<?php

require_once 'PatientNeedWaitingState.php';
require_once 'PatientNeedAcceptedState.php';
require_once '../DB-creation/DBProxy.php';

enum Status: string {
    case Waiting = 'Waiting';
    case Accepted = 'Accepted';
}

class PatientNeed {
    private int $MedicalID;
    private int $PatientID;
    private Status $status;
    private IPatientNeedState $state;
    private DBProxy $dbProxy;

    public function __construct(int $MedicalID, int $PatientID, Status $status = Status::Waiting) {
        $this->MedicalID = $MedicalID;
        $this->PatientID = $PatientID;
        $this->status = $status;
        $this->dbProxy = new DBProxy('PatientNeed');
        $this->initializeState();
    }

    // === Getters ===
    public function getMedicalID(): int {
        return $this->MedicalID;
    }

    public function getPatientID(): int {
        return $this->PatientID;
    }

    public function getStatus(): Status {
        return $this->status;
    }

    public function getState(): IPatientNeedState {
        return $this->state;
    }



    // === Setters ===
    public function setStatus(Status $status): void {
        $this->status = $status;
        $this->initializeState();
    }

    public function setState(IPatientNeedState $state): void {
        $this->state = $state;
    }

    private function initializeState(): void {
        switch ($this->status) {
            case Status::Waiting:
                $this->state = new PatientNeedWaitingState();
                break;
            case Status::Accepted:
                $this->state = new PatientNeedAcceptedState();
                break;
        }
    }

    // === State Transition Logic ===
    public function processPatientNeed(DonationAdmin $admin): void {
        $this->state->handleRequest($this, $admin);
    }

    public function completePatientNeed(DonationAdmin $admin): void {
        $this->state->NextState($this);
        $this->processPatientNeed($admin);
    }


    // === Database Methods ===
    public function createPatientNeed(): bool {
        $query = "INSERT INTO PatientNeed (MedicalID, PatientID, Status) VALUES (?, ?, ?)";
        $stmt = $this->dbProxy->prepare($query, [
            $this->MedicalID,
            $this->PatientID,
            $this->status->value
        ]);

        if (!$stmt) {
            return false;
        }

        return $stmt->execute();
    }

    public function updatePatientNeed(): bool {
        $query = "UPDATE PatientNeed SET Status = ? WHERE MedicalID = ? AND PatientID = ? AND IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($query, [
            $this->status->value,
            $this->MedicalID,
            $this->PatientID
        ]);

        if (!$stmt) {
            return false;
        }

        return $stmt->execute(); 
    }

    public function deletePatientNeed(): bool {
        $query = "UPDATE PatientNeed SET IsDeleted = 1 WHERE MedicalID = ? AND PatientID = ?";
        $stmt = $this->dbProxy->prepare($query, [
            $this->MedicalID,
            $this->PatientID
        ]);

        if (!$stmt) {
            return false;
        }

        return $stmt->execute();
    }

    public function readPatientNeed(int $MedicalID, int $PatientID): ?self {
        $query = "SELECT MedicalID, PatientID, Status FROM PatientNeed WHERE MedicalID = ? AND PatientID = ? AND IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($query, [$MedicalID, $PatientID]);

        if (!$stmt) {
            return null;
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            try {
                $statusEnum = Status::from($row['Status']);
               
            } catch (ValueError) {
                return null;
            }

            return new self((int)$row['MedicalID'], (int)$row['PatientID'], $statusEnum);
        }

        return null;
    }
}
?>
