<?php

class Certificate {
    private int $ID;
    private string $Event_Name;
    private DateTime $Event_Date;
    private string $Volunteer_Name;
    private int $volunteerID;
    private int $eventID;


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

    public function createCertificate(int $volunteerID, int $eventID) {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "INSERT INTO Certificate (VolunteerID, EventID, IsDeleted) VALUES (?, ?, 0)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ii", $volunteerID, $eventID);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function updateCertificate(int $volunteerID, int $eventID){
        $conn = DBConnection::getInstance()->getConnection();

        $query = "UPDATE Certificate SET VolunteerID = ?, EventID = ? WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $id = $this->getID();

        $stmt->bind_param("iii", $volunteerID, $eventID, $id);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public function readCertificate(int $id): ?Certificate {
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
            $this->setID($id);
            $this->setvolunteerID($this->volunteerID); 
            $this->seteventID($this->eventID);         
            $stmt->close();
            return $this;
        }

        $stmt->close();
        return null;
    }

    // Function to delete a certificate record (soft delete)
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

}

?>
