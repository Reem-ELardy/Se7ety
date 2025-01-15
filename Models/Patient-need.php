<?php

require_once 'PatientNeedWaitingState.php';
require_once 'PatientNeedAcceptedState.php';
require_once 'PatientNeedDoneState.php';
require_once '../DB-creation/DBProxy.php';

enum Status: string {
    case Waiting = 'Waiting';
    case Accepted = 'Accepted';
    case Done = 'Done';
}

class PatientNeed {
    private int $MedicalID;
    private int $PatientID;
    private Status $status;
    private IPatientNeedState $state;
    private ?DateTime $acceptedDate; 
    private DBProxy $dbProxy;

    public function __construct(int $MedicalID, int $PatientID, Status $status = Status::Waiting, ?DateTime $acceptedDate = null) {
        $this->MedicalID = $MedicalID;
        $this->PatientID = $PatientID;
        $this->status = $status;
        $this->acceptedDate = $acceptedDate;
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

    public function getAcceptedDate(): ?DateTime {
        return $this->acceptedDate;
    }

    // === Setters ===
    public function setStatus(Status $status): void {
        if ($status === Status::Accepted) {
            $this->acceptedDate = new DateTime(); // Set acceptedDate when transitioning to Accepted
        }
        $this->status = $status;
        $this->initializeState(); // Update the state object to match the new status
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
            case Status::Done:
                $this->state = new PatientNeedDoneState();
                break;
        }
    }

    // === State Transition Logic ===
    public function processPatientNeed(DonationAdmin $admin): void {
        $this->state->handleRequest($this, $admin); // Pass the admin to the state
    }

    public function completePatientNeed(DonationAdmin $admin): void {
        $this->state->NextState($this);
        $this->processPatientNeed($admin); // Pass the required DonationAdmin instance
    }

    public function checkAndTransitionState(): void {
        if ($this->status === Status::Accepted && $this->acceptedDate) {
            $currentDate = new DateTime();
            $interval = $currentDate->diff($this->acceptedDate);

            if ($interval->days >= 3) {
                $this->setStatus(Status::Done); // Transition to Done
                $this->updatePatientNeed(); // Persist the change
            }
        }
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
            return false; // Query preparation failed
        }

        return $stmt->execute(); // Execute the query
    }

    public function updatePatientNeed(): bool {
        $query = "UPDATE PatientNeed SET Status = ? WHERE MedicalID = ? AND PatientID = ? AND IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($query, [
            $this->status->value,
            $this->MedicalID,
            $this->PatientID
        ]);

        if (!$stmt) {
            return false; // Query preparation failed
        }

        return $stmt->execute(); // Execute the query
    }

    public function deletePatientNeed(): bool {
        $query = "UPDATE PatientNeed SET IsDeleted = 1 WHERE MedicalID = ? AND PatientID = ?";
        $stmt = $this->dbProxy->prepare($query, [
            $this->MedicalID,
            $this->PatientID
        ]);

        if (!$stmt) {
            return false; // Query preparation failed
        }

        return $stmt->execute(); // Execute the query
    }

    public function readPatientNeed(int $MedicalID, int $PatientID): ?self {
        $query = "SELECT MedicalID, PatientID, Status FROM PatientNeed WHERE MedicalID = ? AND PatientID = ? AND IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($query, [$MedicalID, $PatientID]);

        if (!$stmt) {
            return null; // Query preparation failed
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            try {
                $statusEnum = Status::from($row['Status']);
                $acceptedDate = $row['AcceptedDate'] ? new DateTime($row['AcceptedDate']) : null;
            } catch (ValueError) {
                return null; // Invalid status value
            }

            return new self((int)$row['MedicalID'], (int)$row['PatientID'], $statusEnum, $acceptedDate);
        }

        return null; // No record found
    }
}
?>
