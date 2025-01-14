<?php

require_once "Event.php";

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

?>