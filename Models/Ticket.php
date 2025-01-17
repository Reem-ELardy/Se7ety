<?php

require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';

class Ticket {
    private $id;
    private $eventID;
    private $patientID;
    private $dateTime;
    private string $Event_Name;
    private string $Patient_Name;
    protected $dbProxy;

    public function __construct($eventID, $patientID, DateTime $dateTime = null) {
        $this->dbProxy = new DBProxy('user');
        $this->eventID = $eventID;
        $this->patientID = $patientID;
        $this->dateTime = $dateTime;
        $this->Event_Name = '';
        $this->Patient_Name = '';

        if ($eventID != 0 && $patientID != 0) {
            $this->fetchEventDetails($eventID);
            $this->fetchPatientName($patientID);
        }
    }

    public function setID($id) {
        $this->id = $id;
    }

    public function getID() {
        return $this->id;
    }

    public function setEventID($eventID) {
        $this->eventID = $eventID;
    }

    public function getEventID() {
        return $this->eventID;
    }

    public function setPatientID($patientID) {
        $this->patientID = $patientID;
    }

    public function setPatientName(string $name): void {
        $this->Patient_Name = $name;
    }
    public function setDateTime(DateTime $dateTime) {
        $this->dateTime = $dateTime;
    }
    public function setEventName(string $name): void {
        $this->Event_Name = $name;
    }

    public function getPatientID() {
        return $this->patientID;
    }

    public function getDateTime() {
        return $this->dateTime;
    }
    public function getPatientName(): string {
        return $this->Patient_Name;
    }
    public function getEventName(): string {
        return $this->Event_Name;
    }
    

    public function createTicket() {
        $query = "INSERT INTO Ticket (EventID, PatientID, date_time) VALUES (?, ?, ?)";
        $dateTimeFormatted = $this->dateTime->format('Y-m-d H:i:s');
        $stmt = $this->dbProxy->prepare($query, [$this->eventID, $this->patientID, $dateTimeFormatted]);

        if (!$stmt) {
            return false;
        }

        $this->id = $this->dbProxy->getInsertId();

        return true;
    }

    public function readTicket() {
        $query = "SELECT t.ID, t.EventID, t.PatientID, t.date_time
                FROM Ticket t
                WHERE t.EventID = ? AND t.PatientID = ? AND t.IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($query, [$this->eventID, $this->patientID]);

        if (!$stmt) {
            return false;
        }

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $ticketData = $result->fetch_assoc();
            $this->id = $ticketData['ID'];
            $this->eventID = $ticketData['EventID'];
            $this->patientID = $ticketData['PatientID'];
            $this->dateTime = new DateTime($ticketData['date_time']);
            return true;
        } else {
            return false;
        }
    }

    public function updateTicket($eventID, $patientID, DateTime $dateTime) {
        $query = "UPDATE Ticket SET 
                    EventID = ?,
                    PatientID = ?,
                    date_time = ?
                  WHERE ID = ?";

        $dateTimeFormatted = $dateTime->format('Y-m-d H:i:s');
        $stmt = $this->dbProxy->prepare($query, [$eventID, $patientID, $dateTimeFormatted, $this->id]);

        if (!$stmt) {
            return false;
        }

        return true;
    }

    public function deleteTicket() {
        $query = "UPDATE Ticket SET IsDeleted = 1 WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->id]);
    
        if (!$stmt) {
            return false;
        }
    
        return true;
    }

    private function fetchEventDetails(int $eventID): void {
        $query = "SELECT Name, Date FROM Event WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$eventID]);
        $eventName = ''; 
        $eventDate = '';
        if ($stmt) {
            $stmt->bind_result($eventName, $eventDate);

            if ($stmt->fetch()) {
                $this->Event_Name = $eventName;
                $this->dateTime = new DateTime($eventDate);
            } else {
                $this->Event_Name = 'Unknown Event';
                $this->dateTime = new DateTime();
            }

        }
    }

    private function fetchPatientName(int $PatientID): void {
        $query = "SELECT Name 
                    FROM Patient 
                    JOIN Person ON Patient.PersonID = Person.ID 
                    WHERE Patient.ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$PatientID]);
        $PatientName = '';
        if ($stmt) {
            $stmt->bind_result($PatientName);

            if ($stmt->fetch()) {
                $this->Patient_Name = $PatientName;
            } else {
                $this->Patient_Name = 'Unknown Patient';
            }
        }
    }
    
}

?>