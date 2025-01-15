<?php

require_once "Event.php";

class EventsModel {

// Function to get all events, including deleted ones
public function getAllEvents(): array {
    $conn = DBConnection::getInstance()->getConnection();
    $id = $maxNoOfAttendance = $locationID = $totalNoPatients = $totalNoVolunteers = 0;
    $name = $date = $description = $type = '';
    $eventType = EventType::Other;
    $events = [];

    $query = "SELECT ID, Name, Date, Description, Type, TotalNoPatients, TotalNoVolunteers, MaxNoOfAttendance, LocationID 
              FROM Event";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return $events;
    }

    $stmt->execute();
    $stmt->bind_result($id, $name, $date, $description, $type, $totalNoPatients, $totalNoVolunteers, $maxNoOfAttendance, $locationID);
    $date_time = new DateTime($date);
    while ($stmt->fetch()) {
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

    $stmt->close();
    return $events;
}

// Function to get all non-deleted events
public function getNonDeletedEvents(): array {
    $conn = DBConnection::getInstance()->getConnection();
    $id = $maxNoOfAttendance = $locationID = $totalNoPatients = $totalNoVolunteers = 0;
    $name = $date = $description = $type = '';
    $eventType = EventType::Other;
    $events = [];

    $query = "SELECT ID, Name, Date, Description, Type, TotalNoPatients, TotalNoVolunteers, MaxNoOfAttendance, LocationID 
              FROM Event WHERE IsDeleted = 0";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return $events;
    }

    $stmt->execute();
    $stmt->bind_result($id, $name, $date, $description, $type, $totalNoPatients, $totalNoVolunteers, $maxNoOfAttendance, $locationID);
    $date_time = new DateTime($date);
    while ($stmt->fetch()) {
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

    $stmt->close();
    return $events;
}

// Function to get upcoming events (non-deleted and date >= current date)
public function getUpcomingEvents(): array {
    $conn = DBConnection::getInstance()->getConnection();
    $id = $maxNoOfAttendance = $locationID = $totalNoPatients = $totalNoVolunteers = 0;
    $name = $date = $description = $type = '';
    $eventType = EventType::Other;
    $events = [];

    $query = "SELECT ID, Name, Date, Description, Type, TotalNoPatients, TotalNoVolunteers, MaxNoOfAttendance, LocationID 
              FROM Event WHERE IsDeleted = 0 AND Date >= CURDATE()";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return $events;
    }

    $stmt->execute();
    $stmt->bind_result($id, $name, $date, $description, $type, $totalNoPatients, $totalNoVolunteers, $maxNoOfAttendance, $locationID);
   
    $date_time = new DateTime($date);
    while ($stmt->fetch()) {
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

    $stmt->close();
    return $events;
}
}

?>