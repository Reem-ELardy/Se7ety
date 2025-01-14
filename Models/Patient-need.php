<?php

require_once 'PatientNeedWaitingState.php';
require_once 'PatientNeedAcceptedState.php';
require_once 'PatientNeedDoneState.php';
require_once '../DB-creation/DB-Connection.php';
require_once 'PatientNeedStateFactory.php';


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

    public function __construct(int $MedicalID, int $PatientID, Status $status = Status::Waiting) {
        $this->MedicalID = $MedicalID;
        $this->PatientID = $PatientID;
        $this->status = $status;
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
    }

    public function setState(IPatientNeedState $state): void {
        $this->state = $state;
    }

    // === State Transition Logic ===
    public function handleRequest(DonationAdmin $admin): void {
        $this->state->handleRequest($this, $admin);
    }

    public function progressState(): void {
        $this->state->progressState($this);
    }

    // === Database Methods ===

    public function createPatientNeed(): bool {
        $conn = DBConnection::getInstance()->getConnection();
        $query = "INSERT INTO PatientNeed (MedicalID, PatientID, Status) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
    
        if (!$stmt) {
            echo "Error preparing statement: " . $conn->error . "\n";
            return false;
        }

        $statusValue = $this->status->value;
        $stmt->bind_param("iis", $this->MedicalID, $this->PatientID, $statusValue);
        $result = $stmt->execute();
    
        if ($result) {
            $this->setState(PatientNeedStateFactory::create($this->status));
        } else {
            echo "Error executing query: " . $stmt->error . "\n";
        }
    
        $stmt->close();
    
        return $result;
    }
    

    public function updatePatientNeed(): bool {
        return $this->executeQuery(
            "UPDATE PatientNeed SET Status = ? WHERE MedicalID = ? AND PatientID = ? AND IsDeleted = 0",
            [$this->status->value, $this->MedicalID, $this->PatientID]
        );
    }

    public function deletePatientNeed(): bool {
        return $this->executeQuery(
            "UPDATE PatientNeed SET IsDeleted = 1 WHERE MedicalID = ? AND PatientID = ?",
            [$this->MedicalID, $this->PatientID]
        );
    }

    public function readPatientNeed(int $MedicalID, int $PatientID): ?self {
        $conn = DBConnection::getInstance()->getConnection();
        $query = "SELECT MedicalID, PatientID, Status FROM PatientNeed WHERE MedicalID = ? AND PatientID = ? AND IsDeleted = 0";
        $stmt = $conn->prepare($query);
    
        if (!$stmt) {
            echo "Error preparing statement: " . $conn->error . "\n";
            return null;
        }
    
        $stmt->bind_param("ii", $MedicalID, $PatientID);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($row = $result->fetch_assoc()) {
            try {
                $statusEnum = Status::from($row['Status']);
            } catch (ValueError $e) {
                echo "Error: Invalid status value '{$row['Status']}' in the database.\n";
                return null;
            }

            $patientNeed = new self((int)$row['MedicalID'], (int)$row['PatientID'], $statusEnum);

            $patientNeed->setState(PatientNeedStateFactory::create($statusEnum));
    
            return $patientNeed;
        }
    
        return null;
    }
    

    private function executeQuery(string $query, array $params): bool {
        $conn = DBConnection::getInstance()->getConnection();
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            echo "Error preparing query: " . $conn->error . "\n";
            return false;
        }

        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        $result = $stmt->execute();

        if (!$result) {
            echo "Error executing query: " . $stmt->error . "\n";
        }

        $stmt->close();
        return $result;
    }


}
