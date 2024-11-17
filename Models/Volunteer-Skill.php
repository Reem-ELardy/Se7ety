<?php

class VolunteerSkill{

    private $volunteerID;
    private $skillID;

    public function createVolunteerSkill() {
        $conn = DBConnection::getInstance()->getConnection();

        // Prepare the query to insert a new Volunteer-Skill association
        $query = "INSERT INTO VolunteerSkills (VolunteerID, SkillID) VALUES (?, ?)";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        // Bind the parameters
        $stmt->bind_param("ii", $this->volunteerID, $this->skillID);

        return $stmt->execute();
    }

    // Method to get all skills for a specific volunteer
    public function getSkillsByVolunteerID() {
        $conn = DBConnection::getInstance()->getConnection();

        // Prepare the query to get skills for a volunteer
        $query = "SELECT s.ID, s.Name 
                FROM VolunteerSkills vs
                JOIN Skills s ON vs.SkillID = s.ID
                WHERE vs.VolunteerID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return null;
        }

        // Bind the parameter
        $stmt->bind_param("i", $this->volunteerID);
        $stmt->execute();
        $result = $stmt->get_result();

        $skills = [];
        while ($row = $result->fetch_assoc()) {
            $skills[] = $row; // Add each skill to the array
        }

        return $skills;
    }

    // Method to get all volunteers for a specific skill
    public function getVolunteersBySkillID() {
        $conn = DBConnection::getInstance()->getConnection();

        // Prepare the query to get volunteers for a skill
        $query = "SELECT v.ID, v.Name 
                FROM VolunteerSkills vs
                JOIN Volunteer v ON vs.VolunteerID = v.ID
                WHERE vs.SkillID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return null;
        }

        // Bind the parameter
        $stmt->bind_param("i", $this->skillID);
        $stmt->execute();
        $result = $stmt->get_result();

        $volunteers = [];
        while ($row = $result->fetch_assoc()) {
            $volunteers[] = $row; // Add each volunteer to the array
        }

        return $volunteers;
    }

    // Method to update a Volunteer-Skill association
    public function updateVolunteerSkill(int $newVolunteerID,int $newSkillID) {
        $conn = DBConnection::getInstance()->getConnection();

        // Prepare the query to update a Volunteer-Skill association
        $query = "UPDATE VolunteerSkills SET VolunteerID = ?, SkillID = ? 
                WHERE VolunteerID = ? AND SkillID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        // Bind the parameters
        $stmt->bind_param("iiii", $newVolunteerID, $newSkillID, $this->volunteerID, $this->skillID);

        return $stmt->execute();
    }

    // Method to delete a Volunteer-Skill association
    public function deleteVolunteerSkill() {
        $conn = DBConnection::getInstance()->getConnection();

        // Prepare the query to delete a Volunteer-Skill association
        $query = "DELETE FROM VolunteerSkills WHERE VolunteerID = ? AND SkillID = ?";
        $stmt = $conn->prepare($query);

        if (!$stmt) {
            return false;
        }

        // Bind the parameters
        $stmt->bind_param("ii", $this->volunteerID, $this->skillID);

        return $stmt->execute();
    }

            // Setter for VolunteerID
    public function setVolunteerID($volunteerID) {
        $this->volunteerID = $volunteerID;
    }

    // Getter for VolunteerID
    public function getVolunteerID() {
        return $this->volunteerID;
    }

    // Setter for SkillID
    public function setSkillID($skillID) {
        $this->skillID = $skillID;
    }

    // Getter for SkillID
    public function getSkillID() {
        return $this->skillID;
    }
}
?>