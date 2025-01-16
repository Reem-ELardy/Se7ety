<?php

require_once "IEventParticipationState.php";
require_once "EventParticipationPendingState.php";
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';

class EventParticipation{

    private int $id;
    private int $volunteerID;
    private int $eventID;
    private string $role;
    private int $participantHours;
    private int $isDeleted;
    private int $isCompleted;
    protected IEventParticipationState $state;
    protected $dbProxy;

    public function __construct(int $volunteerID = 0, int $eventID = 0, string $role = '', int $participantHours = 0) {
        $this->dbProxy = new DBProxy('user');
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
        
        $stmt = $this->dbProxy->prepare($query, [$this->volunteerID, $this->eventID, $this->role, $this->participantHours, $this->isDeleted, $this->isCompleted]);
        if (!$stmt) {
            return false;
        }

        $this->id = $this->dbProxy->getInsertId();

    
        return $stmt;
    }
    
    

    public function readEventParticipation(int $eventId, int $volunteerId) {

        $query = "SELECT ID, VolunteerID, EventID, Role, ParticipantHours, IsDeleted, IsCompleted 
                  FROM EventParticipation WHERE EventID = ? AND VolunteerID = ? AND IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($query, [$eventId, $volunteerId]);
        if (!$stmt) {
            return false;
        }
    
        $stmt->bind_result($this->id, $this->volunteerID, $this->eventID, $this->role, $this->participantHours, $this->isDeleted, $this->isCompleted);
    
        if ($stmt->fetch()) {
            return true;
        } else {
            return false;
        }
    }
    


    public function updateEventParticipation(int $id, int $volunteerID, int $eventID, string $role, int $participantHours) {
   
        $query = "UPDATE EventParticipation SET VolunteerID = ?, EventID = ?, Role = ?, ParticipantHours = ? WHERE ID = ? AND IsDeleted = 0 AND IsCompleted = 0";
        $stmt = $this->dbProxy->prepare($query, [$volunteerID, $eventID, $role, $participantHours, $id]);
        if (!$stmt) {
            return false;
        }

        $this->volunteerID = $volunteerID;
        $this->eventID = $eventID;
        $this->role = $role;
        $this->participantHours = $participantHours;
        return true;
    }
    

    public function deleteEventParticipation() {
  
        $query = "UPDATE EventParticipation SET IsDeleted = 1 WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->id]);
        if (!$stmt) {
            return false;
        }

        return $stmt;
    }

    public function GenerateCertificate(){
        $certificate  = new Certificate( $this->eventID, $this->volunteerID);
        $certificate->createCertificate();
    }

    public function completeParticipation() {

        $query = "UPDATE EventParticipation SET IsCompleted = 1 WHERE ID = ? AND IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($query, [$this->id]);
        if (!$stmt) {
            return false;
        }

        $this->isCompleted = 1;

        return true; 
    }

    public function finishParticipation() {
        $this->state->NextState($this);
        $this->ProcessParticipation();

    }

    public function updateVolunteerHours() {

        $query = "UPDATE Volunteer SET VolunteerHours = VolunteerHours + ? WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->participantHours, $this->volunteerID]);
        if (!$stmt) {
            return false;
        }

        return true; 
    }


}


?>