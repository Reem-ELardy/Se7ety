<?php

require_once "Event.php";
require_once "Observers.php";
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';

class EventReminder implements Observer {
    private int $id;
    private Subject $event;
    private string $reminderMessage;
    protected $dbProxy;

    public function __construct(Subject $event) {
        $this->dbProxy = new DBProxy('user');
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
        $stmt = $this->dbProxy->prepare($sql, [$eventId, $this->reminderMessage, $reminderDate->format('Y-m-d H:i:s')]);

        if ($stmt) {
            $this->id = $this->dbProxy->getInsertId();;
            return true;
        }

        return false;
    }

    // Method to update reminders for a specific event
    public function update(int $eventId, string $name, int $locationId, DateTime $date_time, string $description): bool {

        $updatedMessage = "UPDATED Reminder: The event '$name' is scheduled at $locationId on " . 
                          $date_time->format('Y-m-d H:i:s') . ". Description: $description.";

        $this->reminderMessage = $updatedMessage;
        $sql = "UPDATE EventReminder 
                SET Message = ? 
                WHERE EventID = ? AND IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($sql, [$updatedMessage, $eventId]);

        if ($stmt) {
            return true;
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
        $reminders = [];
        $id = 0;
        $message = '';
        $reminderDate = '';
        $sql = "SELECT ID, Message, ReminderDate FROM EventReminder WHERE EventID = ? AND IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($sql, [$eventId]);
    
        if ($stmt) {
            $stmt->bind_result($id, $message, $reminderDate);
    
            while ($stmt->fetch()) {
                $reminders[] = [
                    'id' => $id,
                    'message' => $message,
                    'reminderDate' => $reminderDate
                ];
            }
        }
    
        return $reminders;
    }
    
}



?>