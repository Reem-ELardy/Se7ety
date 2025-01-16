<?php

require_once "Event.php";
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';

class EventsModel {

    protected $dbProxy;

    public function __construct() {
        $this->dbProxy = new DBProxy('user');
    }

    // Function to get all events, including deleted ones
    public function getAllEvents(): array {
        $id = $maxNoOfAttendance = $locationID = $totalNoPatients = $totalNoVolunteers = 0;
        $name = $date = $description = $type = '';
        $eventType = EventType::Other;
        $events = [];

        $query = "SELECT ID, Name, Date, Description, Type, TotalNoPatients, TotalNoVolunteers, MaxNoOfAttendance, LocationID 
                FROM Event";

        $stmt = $this->dbProxy->prepare($query, []);
        if (!$stmt) {
            return $events;
        }
        $stmt->bind_result($id, $name, $date, $description, $type, $totalNoPatients, $totalNoVolunteers, $maxNoOfAttendance, $locationID);
        
        while ($stmt->fetch()) {
            $date_time = new DateTime($date);
            if ($type == "Donation-Collect") {
                $eventType = EventType::DonationCollect;
            } elseif ($type == "Medical-Tour") {
                $eventType = EventType::MedicalTour;
            } else{
                $eventType = EventType::Other;
            }
            $event = new Event($id, $name, $locationID, $date_time, $description, $maxNoOfAttendance, $eventType);
            $event->setNoOfPatients($totalNoPatients);
            $event->setNoOfVolunteers($totalNoVolunteers);
            $events[] = $event;
        }

        return $events;
    }

    // Function to get all non-deleted events
    public function getNonDeletedEvents(): array {
        $id = $maxNoOfAttendance = $locationID = $totalNoPatients = $totalNoVolunteers = 0;
        $name = $date = $description = $type = '';
        $eventType = EventType::Other;
        $events = [];

        $query = "SELECT ID, Name, Date, Description, Type, TotalNoPatients, TotalNoVolunteers, MaxNoOfAttendance, LocationID 
                FROM Event WHERE IsDeleted = 0";

        $stmt = $this->dbProxy->prepare($query, []);
        if (!$stmt) {
            return $events;
        }

        $stmt->bind_result($id, $name, $date, $description, $type, $totalNoPatients, $totalNoVolunteers, $maxNoOfAttendance, $locationID);

        while ($stmt->fetch()) {
            $date_time = new DateTime($date);
            if ($type === 'Donation-Collect') {
                $eventType = EventType::DonationCollect;
            } elseif ($type === 'Medical-Tour') {
                $eventType = EventType::MedicalTour;
            } else{
                $eventType = EventType::Other;
            }
            $event = new Event($id, $name, $locationID, $date_time, $description, $maxNoOfAttendance, $eventType);
            $event->setNoOfPatients($totalNoPatients);
            $event->setNoOfVolunteers($totalNoVolunteers);
            $events[] = $event;
        }

        return $events;
    }

    // Function to get upcoming events (non-deleted and date >= current date)
    public function getUpcomingEvents(): array {
        $id = $maxNoOfAttendance = $locationID = $totalNoPatients = $totalNoVolunteers = 0;
        $name = $date = $description = $type = '';
        $eventType = EventType::Other;
        $events = [];

        $query = "SELECT ID, Name, Date, Description, Type, TotalNoPatients, TotalNoVolunteers, MaxNoOfAttendance, LocationID 
                FROM Event WHERE IsDeleted = 0 AND Date >= CURDATE()";

        $stmt = $this->dbProxy->prepare($query, []);
        if (!$stmt) {
            return $events;
        }

        $stmt->bind_result($id, $name, $date, $description, $type, $totalNoPatients, $totalNoVolunteers, $maxNoOfAttendance, $locationID);
    
        
        while ($stmt->fetch()) {
            $date_time = new DateTime($date);
            if ($type === 'Donation-Collect') {
                $eventType = EventType::DonationCollect;
            } elseif ($type === 'Medical-Tour') {
                $eventType = EventType::MedicalTour;
            } else{
                $eventType = EventType::Other;
            }
            $event = new Event($id, $name, $locationID, $date_time, $description, $maxNoOfAttendance, $eventType);
            $event->setNoOfPatients($totalNoPatients);
            $event->setNoOfVolunteers($totalNoVolunteers);
            $events[] = $event;
        }

        return $events;
    }

    function getEventsForVolunteer($volunteerID) {
        $sql = "
            SELECT e.ID, e.Name, e.LocationID, e.Date, e.Description, 
                   e.MaxNoOfAttendance, e.Type 
            FROM EventParticipation ep
            INNER JOIN Event e ON ep.EventID = e.ID
            WHERE ep.VolunteerID = ? AND ep.IsDeleted = 0 AND e.IsDeleted = 0
        ";
        $id = $maxNoOfAttendance = $locationID = $totalNoPatients = $totalNoVolunteers = 0;
        $name = $date = $description = $type = '';
        $eventType = EventType::Other;
        $events = [];
    
        $stmt = $this->dbProxy->prepare($sql, [$volunteerID]);

        $stmt->bind_result($id, $name, $locationID, $date, $description, $maxNoOfAttendance, $type);
    
        while ($stmt->fetch()) {
            $date_time = new DateTime($date);
            if ($type === 'Donation-Collect') {
                $eventType = EventType::DonationCollect;
            } elseif ($type === 'Medical-Tour') {
                $eventType = EventType::MedicalTour;
            } else{
                $eventType = EventType::Other;
            }
            $event = new Event($id, $name, $locationID, $date_time, $description, $maxNoOfAttendance, $eventType);
            $event->setNoOfPatients($totalNoPatients);
            $event->setNoOfVolunteers($totalNoVolunteers);
            $events[] = $event;
        }

        return $events;
    }
    



}

?>