<?php

class EventParticipation{

    private int $VolunteerID;
    private int $EventID; 
    private String $Role;
    private int $ParticipantHours;

    
    public function createEventParticipation() {
        $conn = DBConnection::getInstance()->getConnection();
    
        $query = "INSERT INTO EventParticipation (VolunteerID, EventID, Role, ParticipantHours) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
    
        $stmt->bind_param("iisi", $this->VolunteerID, $this->EventID, $this->Role, $$this->ParticipantHours);
        $result = $stmt->execute();
    
    
        return $result;
    }
    

    public function readEventParticipation(int $id) {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "SELECT ID, VolunteerID, EventID, Role, ParticipantHours FROM EventParticipation WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
    
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $stmt->bind_result($this->VolunteerID, $this->EventID, $this->Role, $$this->ParticipantHours);
    
        if ($stmt->fetch()) {
            return true;
        } else {
            return false;
        }
    }


    public function updateEventParticipation(int $id, int $volunteerID, int $eventID, string $role, int $participantHours) {
        $conn = DBConnection::getInstance()->getConnection();
    
        $query = "UPDATE EventParticipation SET VolunteerID = ?, EventID = ?, Role = ?, ParticipantHours = ? WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
    
        $stmt->bind_param("iisii", $volunteerID, $eventID, $role, $participantHours, $id);
        $result = $stmt->execute();
    
        return $result;
    }

    public function deleteEventParticipation(int $id) {
        $conn = DBConnection::getInstance()->getConnection();
    
        // Perform a soft delete by setting the IsDeleted attribute to true
        $query = "UPDATE EventParticipation SET IsDeleted = true WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
    
        // Bind the ID parameter
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
    
        return $result;
    }
}



?>