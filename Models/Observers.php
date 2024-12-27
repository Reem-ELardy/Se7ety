<?php

require_once "Event.php";

interface Observer {
    public function update(int $id, string $name, string $location, DateTime $date_time, string $description);
}


class Notification implements Observer {
    private int $id;
    private int $recieverId;
    private string $message;
    private bool $sent;

     // Getter and Setter for Id
     public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    // Getter and Setter for receiverId
    public function getReceiverId(): int {
        return $this->recieverId;
    }

    public function setReceiverId(int $receiverId): void {
        $this->recieverId = $receiverId;
    }

    // Getter and Setter for message
    public function getMessage(): string {
        return $this->message;
    }

    public function setMessage(string $message): void {
        $this->message = $message;
    }

    // Getter and Setter for sent
    public function isSent(): bool {
        return $this->sent;
    }

    public function setSent(bool $sent): void {
        $this->sent = $sent;
    }
    public function update(
        int $notificationId,
        string $name,
        string $location,
        DateTime $date_time,
        string $description
    ): bool {
        $conn = DBConnection::getInstance()->getConnection();
        // Update the message in the class property
        $this->message = "Event Update: $name at $location on " . $date_time->format('Y-m-d H:i:s') . 
                         ". Description: $description.";
    
        // SQL query to update the message and time in the database
        $sql = "UPDATE Notification 
                SET Message = ?, CreatedAt = CURRENT_TIMESTAMP 
                WHERE ID = ? AND Sent = 0";
    
        $stmt = $conn->prepare($sql);
    
        if ($stmt) {
            // Bind the message and notification ID
            $stmt->bind_param('si', $this->message, $notificationId);
    
            // Execute the query and check the result
            $result = $stmt->execute();
            $stmt->close();
    
            return $result;
        }
    
        return false;
    }
    

    public function createNotification(int $receiverId, string $message): bool {
        $conn = DBConnection::getInstance()->getConnection();
        $sql = "INSERT INTO Notification (ReceiverID, Message, Sent) VALUES (?, ?, 0)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('is', $receiverId, $message);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        return false;
    }

    public function deleteNotification(int $notificationId): bool {
        $conn = DBConnection::getInstance()->getConnection();
        $sql = "DELETE FROM Notification WHERE ID = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('i', $notificationId);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        return false;
    }

    public function getNotificationsByReceiverId(int $receiverId): ?array {
        $conn = DBConnection::getInstance()->getConnection();
        $sql = "SELECT * FROM Notification WHERE ReceiverID = ? AND Sent = 0 ORDER BY CreatedAt DESC";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('i', $receiverId);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $notifications = [];
                while ($row = $result->fetch_assoc()) {
                    $notifications[] = $row;
                }
                $stmt->close();
                return $notifications;
            }
            $stmt->close();
        }

        return null;
    }
}

class EventReminder implements Observer {
    private Subject $event;
    private string $reminderMessage;

    public function __construct(Subject $event) {
        $this->event = $event;
        $this->reminderMessage = "";
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
        $location = $this->event->getDateTime(); 
        $eventDate = $this->event->getDateTime(); 
        $description = $this->event->getDescription(); 

        $this->reminderMessage = "Reminder: The event '$name' is scheduled at $location on " . 
                                  $eventDate->format('Y-m-d H:i:s') . ". Description: $description.";
        
        // Calculate reminder date (1 day before event)
        $reminderDate = $eventDate->sub(new DateInterval('P1D'));

        $sql = "INSERT INTO EventReminder (EventID, Message, ReminderDate) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('iss', $eventId, $this->reminderMessage, $reminderDate->format('Y-m-d H:i:s'));
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        return false;
    }

    // Method to update reminders for a specific event
    public function update(int $eventId, string $name, string $location, DateTime $date_time, string $description): bool {

        $conn = DBConnection::getInstance()->getConnection();

        $updatedMessage = "Reminder: The event '$name' is scheduled at $location on " . 
                          $date_time->format('Y-m-d H:i:s') . ". Description: $description.";

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

        // Method to get event reminders by event ID
        public function getEventReminders(int $eventId): array {

            $conn = DBConnection::getInstance()->getConnection();

            $reminders = [];
            $sql = "SELECT ID, Message, ReminderDate FROM EventReminder WHERE EventID = ? AND IsDeleted = 0";
            $stmt = $conn->prepare($sql);
    
            if ($stmt) {
                $stmt->bind_param('i', $eventId);
                $stmt->execute();
                $stmt->bind_result($id, $message, $reminderDate);
    
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


