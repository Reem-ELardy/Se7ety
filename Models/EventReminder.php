<?php

require_once "Event.php";
require_once "Observers.php";

class EventReminder implements Observer {
    private int $id;
    private Subject $event;
    private string $reminderMessage;

    public function __construct(Subject $event) {
        $this->event = $event;
        $this->reminderMessage = "";
    }

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    // Setter for the event attribute
    public function setEvent(Subject $event): void {
        $this->event = $event;
    }

    // Getter for the event attribute
    public function getEvent(): Subject {
        return $this->event;
    }

    // Setter for the reminderMessage attribute
    public function setReminderMessage(string $reminderMessage): void {
        $this->reminderMessage = $reminderMessage;
    }

    // Getter for the reminderMessage attribute
    public function getReminderMessage(): string {
        return $this->reminderMessage;
    }

    // Method to create a reminder
    public function createReminder(): bool {
        $conn = DBConnection::getInstance()->getConnection();
        // Retrieve event details
        $eventId = $this->event->getId(); 
        $name = $this->event->getName(); 
        $location = $this->event->getLocationID(); 
        $eventDate = $this->event->getDateTime(); 
        $description = $this->event->getDescription(); 

        $this->reminderMessage = "Reminder: The event '$name' is scheduled at $location on " . 
                                  $eventDate->format('Y-m-d') . ". Description: $description.";
        
        // Calculate reminder date (1 day before event)
        $reminderDate = $eventDate->sub(new DateInterval('P1D'));

        $sql = "INSERT INTO EventReminder (EventID, Message, ReminderDate) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('iss', $eventId, $this->reminderMessage, $reminderDate->format('Y-m-d H:i:s'));
            $result = $stmt->execute();
            if ($result) {
                $this->id = $conn->insert_id;
            }
            $stmt->close();
            return $result;
        }

        return false;
    }

    // Method to update reminders for a specific event
    public function update(int $eventId, string $name, int $locationId, DateTime $date_time, string $description): bool {

        $conn = DBConnection::getInstance()->getConnection();

        $updatedMessage = "UPDATED Reminder: The event '$name' is scheduled at $locationId on " . 
                          $date_time->format('Y-m-d H:i:s') . ". Description: $description.";

        $this->reminderMessage = $updatedMessage;
        $sql = "UPDATE EventReminder 
                SET Message = ? 
                WHERE EventID = ? AND IsDeleted = 0";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('si', $updatedMessage, $eventId);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        return false;
    }

    public static function getEventReminderDataById(int $id): ?array {
        $conn = DBConnection::getInstance()->getConnection();
        $sql = "SELECT ID, Message, ReminderDate
            FROM EventReminder
            WHERE ID = ? AND IsDeleted = 0
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($row = $result->fetch_assoc()) {
            return [
                'id' => $row['ID'],
                'message' => $row['Message'],
                'reminderDate' => $row['ReminderDate']
            ];
        }
    
        return null;
    }

    public function getEventReminders(int $eventId): array {
        $conn = DBConnection::getInstance()->getConnection();
    
        $reminders = [];
        $id = 0;
        $message = '';
        $reminderDate = '';
        $sql = "SELECT ID, Message, ReminderDate FROM EventReminder WHERE EventID = ? AND IsDeleted = 0";
        $stmt = $conn->prepare($sql);
    
        if ($stmt) {
            $stmt->bind_param('i', $eventId);
            $stmt->execute();
    
            // Bind the result columns to variables
            $stmt->bind_result($id, $message, $reminderDate);
    
            // Fetch each row and store it in the reminders array
            while ($stmt->fetch()) {
                $reminders[] = [
                    'id' => $id,
                    'message' => $message,
                    'reminderDate' => $reminderDate
                ];
            }
    
            $stmt->close();
        }
    
        return $reminders;
    }
    
}



?>