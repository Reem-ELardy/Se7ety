<?php

require_once "Observers.php";
require_once "Certificate.php";

interface Subject {
    public function registerObserver(Observer $o);
    public function removeObserver(Observer $o);
    public function notifyObserver();
}

class Event implements Subject {
    private int $id;
    private string $name;
    private int $locationID;
    private DateTime $date_time;
    private string $description;
    private int $no_of_volunteers;
    private int $max_no_of_attendance;
    
    /** @var array */
    private array $assigned_volunteers;
    
    /** @var array */
    private array $registerd_patients;
    
    /** @var array */
    private array $observers;

    public function __construct(
        int $id, string $name, int $locationID, DateTime $date_time, 
        string $description, int $no_of_volunteers, int $max_no_of_attendance
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->locationID = $locationID;
        $this->date_time = $date_time;
        $this->description = $description;
        $this->no_of_volunteers = $no_of_volunteers;
        $this->max_no_of_attendance = $max_no_of_attendance;
        $this->assigned_volunteers = [];
        $this->registerd_patients = [];
        $this->observers = [];
    }

    public function getNumberofPatients(){
        return count($this->registerd_patients);
    }

    public function setId(int $id){
        $this->id = $id;
    }

    public function setName(string $name){
        $this->name = $name;
    }

    public function setLocationID(int $locationID){
        $this->locationID = $locationID;
    }

    public function setDateTime(DateTime $date_time){
        $this->date_time = $date_time;
    }

    public function setDescription(string $description){
        $this->description = $description;
    }

    public function setNoOfVolunteers(int $no_of_volunteers) {
        $this->no_of_volunteers = $no_of_volunteers;
    }

    public function setMaxNoOfAttendance(int $max_no_of_attendance){
        $this->max_no_of_attendance = $max_no_of_attendance;
    }

    public function setAssignedVolunteers(array $assigned_volunteers){
        $this->assigned_volunteers = $assigned_volunteers;
    }

    public function setAttendedPatients(array $attended_patients) {
        $this->registerd_patients = $attended_patients;
    }

    public function setObservers(array $observers) {
        $this->observers = $observers;
    }

    public function getId(){
        return $this->id;
    }

    public function getName(){
        return $this->name;
    }

    public function getLocationID(){
        return $this->locationID;
    }

    public function getDateTime(){
        return $this->date_time;
    }

    public function getDescription(){
        return $this->description;
    }

    public function getNoOfVolunteers(){
        return $this->no_of_volunteers;
    }

    public function getMaxNoOfAttendance(){
        return $this->max_no_of_attendance;
    }

    public function getAssignedVolunteers() {
        return $this->assigned_volunteers;
    }

    public function getAttendedPatients() {
        return $this->registerd_patients;
    }

    public function getObservers(){
        return $this->observers;
    }

    public function registerObserver(Observer $o) {
        $this->observers[] = $o;
    }

    public function removeObserver(Observer $o): void {
        $index = array_search($o, $this->observers, true);
        if ($index !== false) {
            unset($this->observers[$index]);
            $this->observers = array_values($this->observers); 
        }
    }

    public function notifyObserver(){
        foreach ($this->observers as $observer) {
            $observer->update($this); 
        }
    }

    public function measurnmentsChanged(){
        $this->notifyObserver();
    }

    public function setMeasurments(string $location, DateTime $date_time, string $description) {
        $this->locationID = $location;
        $this->date_time = $date_time;
        $this->description = $description;
        $this->measurnmentsChanged();
    }

    public function GenerateCertificate(int $volunteerID, int $eventID){
        $certificate  = new Certificate();
        $eventID = $this->id;
        $certificate->createCertificate($volunteerID, $eventID);
    }

    public function createEvent(String $name,  DateTime $date, String $type, int $totalNoPatients, int $totalNoVolunteers, int $locationID) {
        $conn = DBConnection::getInstance()->getConnection();
        
        $query = "INSERT INTO Event (Name, Date, Type, TotalNoPatients, TotalNoVolunteers, LocationID) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
        
        $this->name = $name;
        $this->date_time = $date->format('Y-m-d'); // Format the DateTime object to 'Y-m-d'
        $this->no_of_volunteers = $totalNoVolunteers;
        $this->locationID = $locationID;
    
        $stmt->bind_param("sssiii", $name, $date, $type, $totalNoPatients, $totalNoVolunteers, $locationID);
        
        $result = $stmt->execute();
        if ($result) {
            $this->id = $conn->insert_id;
        }
        
        return $result;
    }

    public function updateEvent() {
        $conn = DBConnection::getInstance()->getConnection();
    
        // Update the Event record
        $query = "UPDATE Event SET Name = ?, Date = ?,  TotalNoVolunteers = ?, LocationID = ? WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
    
        $date = $this->date_time->format('Y-m-d');
        $stmt->bind_param("sssiiii", $this->name, $date,   $this->no_of_volunteers, $this->locationID, $this->id);
        $result = $stmt->execute();
    
        return $result;
    }

    public function readEvent($eventId) {
        $conn = DBConnection::getInstance()->getConnection();
    
        $query = "SELECT ID, Name, Date, Type, TotalNoPatients, TotalNoVolunteers, LocationID 
                  FROM Event 
                  WHERE ID = ?";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
    
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
    
        $stmt->bind_result($this->id, $this->name, $this->date_time, $this->getNumberofPatients(), $this->no_of_volunteers, $this->locationID);
    
        if ($stmt->fetch()) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteEvent($eventId) {
        $conn = DBConnection::getInstance()->getConnection();
    
        if ($eventId === null) {
            return false;
        }
    
        $query = "UPDATE Event SET IsDeleted = true WHERE ID = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("i", $eventId);
        $result = $stmt->execute();
        
        if (!$result) {
            echo "Execute failed: " . $stmt->error;
        } else {
            echo "Event with ID " . $eventId . " marked as deleted.\n";
        }
        
        return $result;
    }
    
}

?>
