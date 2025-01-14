<?php

require_once "Observers.php";
require_once "Certificate.php";

interface Subject {
    public function registerObserver(Observer $o);
    public function removeObserver(Observer $o);
    public function notifyObserver();
    public function getId();
    public function getName();
    public function getLocationID();
    public function getDateTime();
    public function getDescription();
}

enum EventType: string {
    case DonationCollect = 'Donation-Collect';
    case MedicalTour = 'Medical-Tour';
    case Other = 'Other';
}

class Event implements Subject {
    private int $id;
    private string $name;
    private int $locationID;
    private DateTime $date_time;
    private string $description;
    private int $no_of_volunteers;
    private int $no_of_patients;
    private int $max_no_of_attendance;
    private EventType $type;
    private int $isDeleted;

    
    /** @var array */
    private array $observers;

    public function __construct(
        int $id, string $name, int $locationID, DateTime $date_time, 
        string $description, int $max_no_of_attendance, EventType $type
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->locationID = $locationID;
        $this->date_time = $date_time;
        $this->description = $description;
        $this->no_of_volunteers = 0;
        $this->no_of_patients = 0;
        $this->max_no_of_attendance = $max_no_of_attendance;
        $this->type = $type;
        $this->isDeleted = 0;
        $this->observers = [];
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

    public function setNoOfPatients(int $no_of_patients) {
        $this->no_of_patients = $no_of_patients;
    }

    public function setMaxNoOfAttendance(int $max_no_of_attendance){
        $this->max_no_of_attendance = $max_no_of_attendance;
    }

    // Getter for type
    public function getType(): EventType {
        return $this->type;
    }

    // Setter for type
    public function setType(EventType $type): void {
        $this->type = $type;
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

    public function getNoOfPatients(){
        return $this->no_of_patients;
    }

    public function getMaxNoOfAttendance(){
        return $this->max_no_of_attendance;
    }

    public function getObservers(){
        return $this->observers;
    }

    public function createEvent() {
        $conn = DBConnection::getInstance()->getConnection();
        
        $query = "INSERT INTO Event (Name, Date, Description, Type, TotalNoPatients, TotalNoVolunteers, MaxNoOfAttendance, LocationID) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $date = $this->date_time->format('Y-m-d');
        $typeString = $this->type->value;
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param('ssssiiii', $this->name, $date, $this->description, $typeString, $this->no_of_patients, $this->no_of_volunteers, $this->max_no_of_attendance, $this->locationID);
            $result = $stmt->execute();
            if ($result) {
                $this->id = $conn->insert_id;
            }

            $stmt->close();
            $EventReminder = new EventReminder($this);
            $EventReminder->createReminder();
            $this->registerObserver($EventReminder);
            return $result;
        }
    
        return false;
    }

    public function updateEvent(
        string $name, 
        int $locationID, 
        DateTime $date_time, 
        string $description, 
        int $max_no_of_attendance, 
        EventType $type
    ): bool {
        $conn = DBConnection::getInstance()->getConnection();
    
        $query = "UPDATE Event 
                  SET Name = ?, Date = ?, Description = ?, MaxNoOfAttendance = ?, Type = ?, LocationID = ? 
                  WHERE ID = ? AND IsDeleted = 0";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
    
        $date = $date_time->format('Y-m-d');
        $typeString = $type->value;
    
        $stmt->bind_param("sssisii", $name, $date, $description, $max_no_of_attendance, $typeString, $locationID, $this->id);

        $this->setMeasurments($name, $locationID, $date_time, $description, $max_no_of_attendance, $type);
    
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    

    public function readEvent(int $id): bool {
        $conn = DBConnection::getInstance()->getConnection();
    
        $query = "SELECT ID, Name, Date, Description, Type, TotalNoPatients, TotalNoVolunteers, MaxNoOfAttendance, LocationID 
                  FROM Event 
                  WHERE ID = ? AND IsDeleted = 0";
    
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
        
        $name = $date = $description = $type = '';
        $totalNoPatients = $totalNoVolunteers = $maxNoOfAttendance = $locationID = 0;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($id, $name, $date, $description, $type, $totalNoPatients, $totalNoVolunteers, $maxNoOfAttendance, $locationID);
    
        if ($stmt->fetch()) {
            $this->id = $id;
            $this->name = $name;
            $this->locationID = $locationID;
            $this->date_time = new DateTime($date);
            $this->description = $description;
            $this->no_of_volunteers = $totalNoVolunteers;
            $this->no_of_patients = $totalNoPatients;
            $this->max_no_of_attendance = $maxNoOfAttendance;
            $this->type = match($type) {
                'Donation-Collect' => EventType::DonationCollect,
                'Medical-Tour' => EventType::MedicalTour,
                'Other' => EventType::Other,
                default => throw new InvalidArgumentException("Invalid event type: $type"),
            };
    
            $stmt->close();
            return true;
        } else {
            $stmt->close();
            return false;
        }
    }
    
    public function deleteEvent() {
        $conn = DBConnection::getInstance()->getConnection();
        
        $query = "UPDATE Event SET IsDeleted = 1 WHERE ID = ?";
        $stmt = $conn->prepare($query);
        
        if (!$stmt) {
            return false;
        }
        
        $stmt->bind_param("i", $this->id);
        $this->isDeleted = 1;
        $result = $stmt->execute();

    }


    public function registerObserver(Observer $o): void {
        $conn = DBConnection::getInstance()->getConnection();
        $observerType = ($o instanceof Notification) ? 0 : 1;
        $observerId = $o->getId();
    
        $stmt = $conn->prepare("
            INSERT INTO Observer (EventID, ObserverID, Type, IsDeleted)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param('iiii', $this->id, $observerId ,$observerType, $this->isDeleted);
        $stmt->execute();
    }
    
    public function removeObserver(Observer $o): void {

        $conn = DBConnection::getInstance()->getConnection();

        $observerType = ($o instanceof Notification) ? 0 : 1;
        $observerId = $o->getId();
    
        $stmt = $conn->prepare("
            UPDATE Observer 
            SET IsDeleted = 1
            WHERE EventID = ? AND Type = ? AND ObserverID = ? AND IsDeleted = 0
        ");
        $stmt->bind_param('iii', $this->id, $observerId ,$observerType);
        $stmt->execute();
    }


    public function fetchObserversData(): array {
        $conn = DBConnection::getInstance()->getConnection();
        $observersData = [];
        $sql = "SELECT ID, Type, ObserverID FROM Observer WHERE EventID = ? AND IsDeleted = 0";
        $stmt = $conn->prepare($sql);
    
        if ($stmt) {
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            $stmt->bind_result($id, $type, $observerId);
    
            while ($stmt->fetch()) {
                $observersData[] = ['id' => $id, 'type' => $type, 'observerId' => $observerId];
            }
    
            $stmt->close();
        }
    
        return $observersData;
    }
    
    public function getEventObservers(): void {
        $observers = [];
        $observersData = $this->fetchObserversData();
        foreach ($observersData as $data) {
            if ($data['type'] == 0) { // Notification
                $notification = Notification::getNotificationById($data['observerId']);
                if ($notification !== null) {
                    $observers[] = $notification;
                }
            } elseif ($data['type'] == 1) { // EventReminder
                $reminderData = EventReminder::getEventReminderDataById($data['observerId']);
                if ($reminderData !== null) {
                    $reminder = new EventReminder($this); // Assuming Subject class has a constructor that takes eventId
                    $reminder->setId($reminderData['id']);
                    $reminder->setReminderMessage($reminderData['message']);
                    $observers[] = $reminder;
                }
            }
        }
    
        $this->observers = $observers;
    }
    
    

    public function notifyObserver() {
        $this->getEventObservers();
        foreach ($this->observers as $observer) {
            $observer->update($this->id, $this->name, $this->locationID, $this->date_time, $this->description); 
        }
    }
    

    public function measurnmentsChanged(){
        $this->notifyObserver();
    }

    public function setMeasurments(string $name, int $locationID, DateTime $date_time, string $description, int $max_no_of_attendance, EventType $type) {

        $this->name = $name;
        $this->locationID = $locationID;
        $this->date_time = $date_time;
        $this->description = $description;
        $this->max_no_of_attendance = $max_no_of_attendance;
        $this->type = $type;
        $this->measurnmentsChanged();
    }


    public function getNumberofPatients(): int {
        $conn = DBConnection::getInstance()->getConnection();
        $count = 0;
        $sql = "SELECT COUNT(*) FROM PatientEvent WHERE EventID = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('i', $this->id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
            return $count;
        }

        return $count; 
    }

    public function addVolunteer(): bool {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "UPDATE Event SET TotalNoVolunteers = TotalNoVolunteers + 1 WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $this->id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }


    public function removeVolunteer(): bool {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "UPDATE Event SET TotalNoVolunteers = TotalNoVolunteers - 1 WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $this->id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }


    public function addPatient(): bool {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "UPDATE Event SET TotalNoPatients = TotalNoPatients + 1 WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $this->id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function removePatient(): bool {
        $conn = DBConnection::getInstance()->getConnection();

        $query = "UPDATE Event SET TotalNoPatients = TotalNoPatients - 1 WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $this->id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function addPatientToEvent(int $patientId): void {
        $this->no_of_patients = $this->getNumberofPatients();
        if($this->no_of_patients < $this->max_no_of_attendance){
            $PatientEvent  = new PatientEvent($this->id, $patientId);
            $create = $PatientEvent->create();
            if($create){
                $this->addPatient();
                $PatientNotification = new Notification($patientId, "You have been added to the event: ".$this->name);
                $PatientNotification->createNotification();
                $this->registerObserver($PatientNotification);
            }
        }
    }

    public function addVolunteerToEvent(int $volunteerID, string $role, int $participantHours): void {
        $eventParticipation  = new EventParticipation($volunteerID, $this->id, $role, $participantHours);
        $create = $eventParticipation->createEventParticipation();
        if($create){
            $this->addVolunteer();
            $VolunteerNotification = new Notification($volunteerID, "You have been added to the event: ".$this->name);
            $VolunteerNotification->createNotification();
            $this->registerObserver($VolunteerNotification);
        }
    }

    public function deletePatientFromEvent(int $patientId): void {
        $PatientEvent  = new PatientEvent($this->id, $patientId);
        $read = $PatientEvent->read($this->id, $patientId);
        if($read){
            $delete = $PatientEvent->delete();
            if($delete){
                $this->removePatient();
                $PatientNotification = new Notification($patientId, "You have been deleted from the event: ".$this->name);
                $PatientNotification->createNotification();
                $this->registerObserver($PatientNotification);
            }
        }
    }

    public function deleteVolunteerFromEvent(int $volunteerId): void {
        $eventParticipation  = new EventParticipation(0, 0, "", 0);
        $read = $eventParticipation->readEventParticipation($this->id, $volunteerId);
        if($read){
            $delete = $eventParticipation->deleteEventParticipation();
            if($delete){
                $this->removeVolunteer();
                $VolunteerNotification = new Notification($volunteerId, "You have been deleted from the event: ".$this->name);
                $VolunteerNotification->createNotification();
                $this->registerObserver($VolunteerNotification);
            }
        }
    }
    
}


class EventsModel {

    // Function to get all events, including deleted ones
    public function getAllEvents(): array {
        $conn = DBConnection::getInstance()->getConnection();
        $id = $maxNoOfAttendance = $locationID = $totalNoPatients = $totalNoVolunteers = 0;
        $name = $date = $description = $type = '';
        $events = [];

        $query = "SELECT ID, Name, Date, Description, Type, TotalNoPatients, TotalNoVolunteers, MaxNoOfAttendance, LocationID 
                  FROM Event";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return $events;
        }

        $stmt->execute();
        $stmt->bind_result($id, $name, $date, $description, $type, $totalNoPatients, $totalNoVolunteers, $maxNoOfAttendance, $locationID);
        $type = match($type) {
            'Donation-Collect' => EventType::DonationCollect,
            'Medical-Tour' => EventType::MedicalTour,
            'Other' => EventType::Other,
            default => throw new InvalidArgumentException("Invalid event type: $type"),
        };
        $date_time = new DateTime($date);
        while ($stmt->fetch()) {
            $event = new Event($id, $name, $locationID, $date_time, $description, $maxNoOfAttendance, $type);
            $event->setNoOfPatients($totalNoPatients);
            $event->setNoOfVolunteers($totalNoVolunteers);
            $events[] = $event;
        }

        $stmt->close();
        return $events;
    }

    // Function to get all non-deleted events
    public function getNonDeletedEvents(): array {
        $conn = DBConnection::getInstance()->getConnection();
        $id = $maxNoOfAttendance = $locationID = $totalNoPatients = $totalNoVolunteers = 0;
        $name = $date = $description = $type = '';
        $events = [];

        $query = "SELECT ID, Name, Date, Description, Type, TotalNoPatients, TotalNoVolunteers, MaxNoOfAttendance, LocationID 
                  FROM Event WHERE IsDeleted = 0";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return $events;
        }

        $stmt->execute();
        $stmt->bind_result($id, $name, $date, $description, $type, $totalNoPatients, $totalNoVolunteers, $maxNoOfAttendance, $locationID);
        $type = match($type) {
            'Donation-Collect' => EventType::DonationCollect,
            'Medical-Tour' => EventType::MedicalTour,
            'Other' => EventType::Other,
            default => throw new InvalidArgumentException("Invalid event type: $type"),
        };
        $date_time = new DateTime($date);
        while ($stmt->fetch()) {
            $event = new Event($id, $name, $locationID, $date_time, $description, $maxNoOfAttendance, $type);
            $event->setNoOfPatients($totalNoPatients);
            $event->setNoOfVolunteers($totalNoVolunteers);
            $events[] = $event;
        }

        $stmt->close();
        return $events;
    }

    // Function to get upcoming events (non-deleted and date >= current date)
    public function getUpcomingEvents(): array {
        $conn = DBConnection::getInstance()->getConnection();
        $id = $maxNoOfAttendance = $locationID = $totalNoPatients = $totalNoVolunteers = 0;
        $name = $date = $description = $type = '';
        $events = [];

        $query = "SELECT ID, Name, Date, Description, Type, TotalNoPatients, TotalNoVolunteers, MaxNoOfAttendance, LocationID 
                  FROM Event WHERE IsDeleted = 0 AND Date >= CURDATE()";

        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return $events;
        }

        $stmt->execute();
        $stmt->bind_result($id, $name, $date, $description, $type, $totalNoPatients, $totalNoVolunteers, $maxNoOfAttendance, $locationID);
        $type = match($type) {
            'Donation-Collect' => EventType::DonationCollect,
            'Medical-Tour' => EventType::MedicalTour,
            'Other' => EventType::Other,
            default => throw new InvalidArgumentException("Invalid event type: $type"),
        };
        $date_time = new DateTime($date);
        while ($stmt->fetch()) {
            $event = new Event($id, $name, $locationID, $date_time, $description, $maxNoOfAttendance, $type);
            $event->setNoOfPatients($totalNoPatients);
            $event->setNoOfVolunteers($totalNoVolunteers);
            $events[] = $event;
        }

        $stmt->close();
        return $events;
    }
}