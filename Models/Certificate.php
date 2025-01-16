<?php

class Certificate {
    private int $ID;
    private string $Event_Name;
    private DateTime $Event_Date;
    private string $Volunteer_Name;
    private int $volunteerID;
    private int $eventID;

    public function __construct(int $eventID, int $volunteerID) {
        $this->eventID = $eventID;
        $this->volunteerID = $volunteerID;
        $this->ID = 0;
        $this->Event_Name = '';
        $this->Event_Date = new DateTime();
        $this->Volunteer_Name = '';

        if ($eventID != 0 && $volunteerID != 0) {
            $this->fetchEventDetails($eventID);
            $this->fetchVolunteerName($volunteerID);
        }
    }
    

    public function setvolunteerID(int $volunteerID) {
        $this->volunteerID = $volunteerID;
    }

    public function getvolunteerID() {
        return $this->volunteerID;
    }

    public function seteventID(int $eventID): void {
        $this->eventID = $eventID;
    }

    public function geteventID(): int {
        return $this->eventID;
    }

    public function setID(int $ID): void {
        $this->ID = $ID;
    }

    public function getID(): int {
        return $this->ID;
    }

    public function setEventName(string $Event_Name): void {
        $this->Event_Name = $Event_Name;
    }

    public function getEventName(): string {
        return $this->Event_Name;
    }

    public function setEventDate(DateTime $Event_Date): void {
        $this->Event_Date = $Event_Date;
    }

    public function getEventDate(): DateTime {
        return $this->Event_Date;
    }

    public function setVolunteerName(string $Volunteer_Name): void {
        $this->Volunteer_Name = $Volunteer_Name;
    }

    public function getVolunteerName(): string {
        return $this->Volunteer_Name;
    }

    public function createCertificate() {
        $conn = DBConnection::getInstance()->getConnection();
        $query = "INSERT INTO Certificate (VolunteerID, EventID, IsDeleted) VALUES (?, ?, 0)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ii", $this->volunteerID, $this->eventID);
        $result = $stmt->execute();
        if ($result) {
            $this->ID = $conn->insert_id;
        }

        $stmt->close();
        return $result;

        return $result;
    }

    // public function updateCertificate(int $volunteerID, int $eventID){
    //     $conn = DBConnection::getInstance()->getConnection();

    //     $query = "UPDATE Certificate SET VolunteerID = ?, EventID = ? WHERE ID = ?";
    //     $stmt = $conn->prepare($query);
    //     if (!$stmt) {
    //         return false;
    //     }

    //     $id = $this->getID();

    //     $stmt->bind_param("iii", $volunteerID, $eventID, $id);
    //     $result = $stmt->execute();
    //     $stmt->close();

    //     return $result;
    // }

    public function readCertificate(int $id): bool {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "SELECT VolunteerID, EventID FROM Certificate WHERE ID = ? AND IsDeleted = 0";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($this->volunteerID, $this->eventID);
        $stmt->fetch();

        if ($this->volunteerID && $this->eventID) {
            $this->ID = $id;
            $this->fetchEventDetails($this->eventID);
            $this->fetchVolunteerName($this->volunteerID);         
            $stmt->close();
            return true;
        }

        $stmt->close();
        return false;
    }

    public function getCertificatesByVolunteerId(int $volunteerId): ?array {
        $conn = DBConnection::getInstance()->getConnection();
        $sql = "SELECT * FROM Certificate WHERE VolunteerID = ? AND IsDeleted = 0";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('i', $volunteerId);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $certificates = [];
                while ($row = $result->fetch_assoc()) {
                    $certificates[] = $row;
                }
                $stmt->close();
                return $certificates;
            }
            $stmt->close();
        }

        return null;
    }

    public function deleteCertificate(): bool {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "UPDATE Certificate SET IsDeleted = 1 WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $id = $this->getID();
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
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
                $this->Event_Date = new DateTime($eventDate);
            } else {
                $this->Event_Name = 'Unknown Event';
                $this->Event_Date = new DateTime();
            }


            $stmt->close();
        }
    }

    private function fetchVolunteerName(int $volunteerID): void {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "SELECT Name 
                    FROM Volunteer 
                    JOIN Person ON Volunteer.PersonID = Person.ID 
                    WHERE Volunteer.ID = ?";
        $stmt = $conn->prepare($query);
        $volunteerName = '';
        if ($stmt) {
            $stmt->bind_param("i", $volunteerID);
            $stmt->execute();
            $stmt->bind_result($volunteerName);

            if ($stmt->fetch()) {
                // Volunteer name is now set in the class
                $this->Volunteer_Name = $volunteerName;
            } else {
                $this->Volunteer_Name = 'Unknown Volunteer';
            }


            $stmt->close();
        }
    }

    public function downloadCertificate(): bool {
        $jsonAdapter = new CertificateToJSON($this);
        return $jsonAdapter->exportToJson();
    }

}

?>
