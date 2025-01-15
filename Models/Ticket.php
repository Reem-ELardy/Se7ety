<?php

class Ticket {
    private $id;
    private $eventID;
    private $patientID;
    private $dateTime;
    private string $Event_Name;
    private string $Patient_Name;

    public function __construct($eventID, $patientID, DateTime $dateTime = null) {
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
        $conn = DBConnection::getInstance()->getConnection();

        $query = "INSERT INTO Ticket (EventID, PatientID, date_time) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $dateTimeFormatted = $this->dateTime->format('Y-m-d H:i:s');
        $stmt->bind_param("iis", $this->eventID, $this->patientID, $dateTimeFormatted);

        $result = $stmt->execute();
        if ($result) {
            $this->id = $conn->insert_id;
        }

        return $result;
    }

    public function readTicket() {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "SELECT t.ID, t.EventID, t.PatientID, t.date_time
                FROM Ticket t
                WHERE t.EventID = ? AND t.PatientID = ? AND t.IsDeleted = 0";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ii", $this->eventID, $this->patientID);
        $stmt->execute();
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
        $conn = DBConnection::getInstance()->getConnection();

        $query = "UPDATE Ticket SET 
                    EventID = ?,
                    PatientID = ?,
                    date_time = ?
                  WHERE ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $dateTimeFormatted = $dateTime->format('Y-m-d H:i:s');
        $stmt->bind_param("iisi", $eventID, $patientID, $dateTimeFormatted, $this->id);

        return $stmt->execute();
    }

    public function deleteTicket() {
        $conn = DBConnection::getInstance()->getConnection();
    
        $query = "UPDATE Ticket SET IsDeleted = 1 WHERE ID = ?";
        $stmt = $conn->prepare($query);
    
        if (!$stmt) {
            return false;
        }
    
        $stmt->bind_param("i", $this->id);
    
        return $stmt->execute();
    }

    private function fetchEventDetails(int $eventID): void {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "SELECT Name, Date FROM Event WHERE ID = ?";
        $stmt = $conn->prepare($query);
        $eventName = ''; 
        $eventDate = '';
        if ($stmt) {
            $stmt->bind_param("i", $eventID);
            $stmt->execute();
            $stmt->bind_result($eventName, $eventDate);

            if ($stmt->fetch()) {
                $this->Event_Name = $eventName;
                $this->dateTime = new DateTime($eventDate);
            } else {
                $this->Event_Name = 'Unknown Event';
                $this->dateTime = new DateTime();
            }


            $stmt->close();
        }
    }

    private function fetchPatientName(int $PatientID): void {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "SELECT Name 
                    FROM Patient 
                    JOIN Person ON Patient.PersonID = Person.ID 
                    WHERE Patient.ID = ?";
        $stmt = $conn->prepare($query);
        $PatientName = '';
        if ($stmt) {
            $stmt->bind_param("i", $PatientID);
            $stmt->execute();
            $stmt->bind_result($PatientName);

            if ($stmt->fetch()) {
                $this->Patient_Name = $PatientName;
            } else {
                $this->Patient_Name = 'Unknown Patient';
            }


            $stmt->close();
        }
    }
    
}

?>