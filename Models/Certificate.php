<?php

require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';

class Certificate {
    private int $ID;
    private string $Event_Name;
    private DateTime $Event_Date;
    private string $Volunteer_Name;
    private int $volunteerID;
    private int $eventID;
    protected $dbProxy;

    public function __construct(int $eventID, int $volunteerID) {
        $this->dbProxy = new DBProxy('user');
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
        $query = "INSERT INTO Certificate (VolunteerID, EventID, IsDeleted) VALUES (?, ?, 0)";
        $stmt = $this->dbProxy->prepare($query, [$this->volunteerID, $this->eventID]);
        if (!$stmt) {
            return false;
        }

        $this->ID = $this->dbProxy->getInsertId();

        return true;
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
        $query = "SELECT VolunteerID, EventID FROM Certificate WHERE ID = ? AND IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($query, [$id]);
        if (!$stmt) {
            return null;
        }

        $stmt->bind_result($this->volunteerID, $this->eventID);

        if ($stmt->fetch()) {
            if ($this->volunteerID && $this->eventID) {
                $this->ID = $id;
                $this->fetchEventDetails($this->eventID);
                $this->fetchVolunteerName($this->volunteerID);         
                return true;
            }
        }

        return false;
    }

    public function getCertificatesByVolunteerId(int $volunteerId): ?array {
        $sql = "SELECT ID, VolunteerID, EventID FROM Certificate WHERE VolunteerID = ? AND IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($sql, [$volunteerId]);

        $id = $volunteerID = $eventID = 0;
        $certificates = [];

        $stmt->bind_result($id, $volunteerID, $eventID);
    
        while ($stmt->fetch()) {
            $certificate = new Certificate($eventID, $volunteerID);
            $certificate->setID($id);
            $certificates[] = $certificate;
        }

        return $certificates;
    }

    public function deleteCertificate(): bool {

        $query = "UPDATE Certificate SET IsDeleted = 1 WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->ID]);
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
                $this->Event_Date = new DateTime($eventDate);
            } else {
                $this->Event_Name = 'Unknown Event';
                $this->Event_Date = new DateTime();
            }
        }
    }

    private function fetchVolunteerName(int $volunteerID): void {

        $query = "SELECT Name 
                    FROM Volunteer 
                    JOIN Person ON Volunteer.PersonID = Person.ID 
                    WHERE Volunteer.ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$volunteerID]);
        $volunteerName = '';
        if ($stmt) {
            $stmt->bind_result($volunteerName);

            if ($stmt->fetch()) {
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

    public function generateCertificateContent(): string {
        return "Certificate of Participation\n" .
               "Volunteer Name: " . $this->getVolunteerName() . "\n" .
               "Event Name: " . $this->getEventName() . "\n" .
               "Event Date: " . $this->getEventDate()->format('Y-m-d') . "\n\n" .
               "Thank you for your contribution to making this event a success!";
    }

}

?>
