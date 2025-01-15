<?php

require_once "IEventParticipationState.php";
require_once "EventParticipationPendingState.php";

class EventParticipation{

    private int $id;
    private int $volunteerID;
    private int $eventID;
    private string $role;
    private int $participantHours;
    private int $isDeleted;
    private int $isCompleted;
    protected IEventParticipationState $state;

    public function __construct(int $volunteerID = 0, int $eventID = 0, string $role = '', int $participantHours = 0) {
        $this->id = 0;
        $this->volunteerID = $volunteerID;
        $this->eventID = $eventID;
        $this->role = $role;
        $this->participantHours = $participantHours;
        $this->isDeleted = 0;
        $this->isCompleted = 0;
        $this->state = new ParticipationPendingState();
    }

    // Getters and setters for the attributes
    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getVolunteerID(): int {
        return $this->volunteerID;
    }

    public function setVolunteerID(int $volunteerID): void {
        $this->volunteerID = $volunteerID;
    }

    public function getEventID(): int {
        return $this->eventID;
    }

    public function setEventID(int $eventID): void {
        $this->eventID = $eventID;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function setRole(string $role): void {
        $this->role = $role;
    }

    public function getParticipantHours(): int {
        return $this->participantHours;
    }

    public function setParticipantHours(int $participantHours): void {
        $this->participantHours = $participantHours;
    }

    public function getIsDeleted(): int {
        return $this->isDeleted;
    }

    public function setIsDeleted(int $isDeleted): void {
        $this->isDeleted = $isDeleted;
    }

    public function getIsCompleted(): int {
        return $this->isCompleted;
    }

    public function setIsCompleted(int $isCompleted): void {
        $this->isCompleted = $isCompleted;
    }

    public function SetState($state){
        $this->state=$state;
    }

    public function ProcessParticipation(): void{
        $this->state->ProsscingParticipation($this);
    }

    public function createEventParticipation(): bool {
        $conn = DBConnection::getInstance()->getConnection();
    
        $query = "INSERT INTO EventParticipation (VolunteerID, EventID, Role, ParticipantHours, IsDeleted, IsCompleted) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
    
        // Bind parameters including the new attributes 'IsDeleted' and 'IsCompleted'
        $stmt->bind_param("iisiii", $this->volunteerID, $this->eventID, $this->role, $this->participantHours, $this->isDeleted, $this->isCompleted);
        $result = $stmt->execute();
        
        if ($result) {
            // After successful execution, update the 'id' of the object with the generated ID from the database
            $this->id = $conn->insert_id;
            $stmt->close();
        }
    
        return $result;
    }
    
    

    public function readEventParticipation(int $eventId, int $volunteerId) {
        $conn = DBConnection::getInstance()->getConnection();
    
        $query = "SELECT ID, VolunteerID, EventID, Role, ParticipantHours, IsDeleted, IsCompleted 
                  FROM EventParticipation WHERE EventID = ? AND VolunteerID = ? AND IsDeleted = 0";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
    
        $stmt->bind_param("ii", $eventId, $volunteerId);
        $stmt->execute();
    
        $stmt->bind_result($this->id, $this->volunteerID, $this->eventID, $this->role, $this->participantHours, $this->isDeleted, $this->isCompleted);
    
        if ($stmt->fetch()) {
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }
    


    public function updateEventParticipation(int $id, int $volunteerID, int $eventID, string $role, int $participantHours) {
        $conn = DBConnection::getInstance()->getConnection();
    
        $query = "UPDATE EventParticipation SET VolunteerID = ?, EventID = ?, Role = ?, ParticipantHours = ? WHERE ID = ? AND IsDeleted = 0 AND IsCompleted = 0";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
    
        // Binding the parameters, including the new IsDeleted and IsCompleted fields
        $stmt->bind_param("iisiiii", $volunteerID, $eventID, $role, $participantHours, $id);
        $result = $stmt->execute();
    
        // Update the object with the new values
        if ($result) {
            $this->volunteerID = $volunteerID;
            $this->eventID = $eventID;
            $this->role = $role;
            $this->participantHours = $participantHours;
            return true;
        }
    
        return false;
    }
    

    public function deleteEventParticipation() {
        $conn = DBConnection::getInstance()->getConnection();
    
        $query = "UPDATE EventParticipation SET IsDeleted = 1 WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
    
        // Bind the ID parameter
        $stmt->bind_param("i", $this->id);
        $result = $stmt->execute();
        $stmt->close();
    
        return $result;
    }

    public function GenerateCertificate(){
        $certificate  = new Certificate( $this->eventID, $this->volunteerID);
        $certificate->createCertificate();
    }

    public function completeParticipation() {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "UPDATE EventParticipation SET IsCompleted = 1 WHERE ID = ? AND IsDeleted = 0";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $stmt->close();


        $this->isCompleted = 1;

        return true; 
    }

    public function finishParticipation() {
        $this->state->NextState($this);
        $this->ProcessParticipation();

    }

    public function updateVolunteerHours() {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "UPDATE Volunteer SET VolunteerHours = VolunteerHours + ? WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ii", $this->participantHours, $this->volunteerID);
        $stmt->execute();
        $stmt->close();

        return true; 
    }


}


?>