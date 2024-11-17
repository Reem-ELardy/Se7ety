<?php

class Ticket {
    private $id;
    private $eventName;
    private $patientName;
    private $dateTime;

    public function __construct($eventName = null, $patientName = null, DateTime $dateTime = null) {
        $this->eventName = $eventName;
        $this->patientName = $patientName;
        $this->dateTime = $dateTime;
    }


    public function setID($id) {
        $this->id = $id;
    }

    public function getID() {
        return $this->id;
    }

    public function setEventName($eventName) {
        $this->eventName = $eventName;
    }

    public function getEventName() {
        return $this->eventName;
    }

    public function setPatientName($patientName) {
        $this->patientName = $patientName;
    }

    public function getPatientName() {
        return $this->patientName;
    }

    public function setDateTime(DateTime $dateTime) {
        $this->dateTime = $dateTime;
    }

    public function getDateTime() {
        return $this->dateTime;
    }

    public function createTicket() {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "INSERT INTO Ticket (EventID, PatientID, date_time) VALUES (
                    (SELECT ID FROM Event WHERE Name = ?),
                    (SELECT ID FROM Patient WHERE Name = ?),
                    ?)";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        // Format the DateTime object to 'Y-m-d H:i:s'
        $dateTimeFormatted = $this->dateTime->format('Y-m-d H:i:s');

        $stmt->bind_param("sss", $this->eventName, $this->patientName, $dateTimeFormatted);

        $result = $stmt->execute();
        if ($result) {
            $this->id = $conn->insert_id;
        }

        return $result;
    }

    public function getTicketByID($id) {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "SELECT t.ID, e.Name AS EventName, p.Name AS PatientName, t.date_time
                  FROM Ticket t
                  JOIN Event e ON t.EventID = e.ID
                  JOIN Patient p ON t.PatientID = p.ID
                  WHERE t.ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return null;
        }
    }


    public function updateTicket($id, $eventName, $patientName, DateTime $dateTime) {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "UPDATE Ticket SET 
                    EventID = (SELECT ID FROM Event WHERE Name = ?),
                    PatientID = (SELECT ID FROM Patient WHERE Name = ?),
                    date_time = ?
                  WHERE ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $dateTimeFormatted = $dateTime->format('Y-m-d H:i:s');

        $stmt->bind_param("sssi", $eventName, $patientName, $dateTimeFormatted, $id);

        return $stmt->execute();
    }


    public function deleteTicket($id) {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "DELETE FROM Ticket WHERE ID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);

        return $stmt->execute();
    }

}

?>
