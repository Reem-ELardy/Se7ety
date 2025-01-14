<?php

require_once "Event.php";
require_once "Observers.php";

class Notification implements Observer {
    private int $id;
    private int $recieverId;
    private string $message;
    private bool $sent;

    public function __construct(int $receiverId, string $message) {
        $this->recieverId = $receiverId;
        $this->message = $message;
        $this->sent = false;
    }

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

    public function update(int $eventId, string $name, int $locationId, DateTime $date_time, string $description): bool {
        $conn = DBConnection::getInstance()->getConnection();
        // Update the message in the class property
        $updatedMessage = "Event Update: $name at $locationId on " . $date_time->format('Y-m-d H:i:s'). 
                         ". Description: $description.";
        
        $this->message = $updatedMessage;
        // SQL query to update the message and time in the database
        $sql = "UPDATE Notification 
                SET Message = ? 
                WHERE ID = ? AND IsDeleted = 0";
    
        $stmt = $conn->prepare($sql);
    
        if ($stmt) {
            // Bind the message and notification ID
            $stmt->bind_param('si', $updatedMessage, $this->id);
    
            // Execute the query and check the result
            $result = $stmt->execute();
            $stmt->close();
    
            return $result;
        }
    
        return false;
    }
    

    public function createNotification(): bool {
        $conn = DBConnection::getInstance()->getConnection();
        $sql = "INSERT INTO Notification (ReceiverID, Message, Sent) VALUES (?, ?, 0)";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('is', $this->recieverId, $this->message);
            $result = $stmt->execute();
            if ($result) {
                $this->id = $conn->insert_id;
            }
            $stmt->close();
            return $result;
        }

        return false;
    }


    public function deleteNotification(int $notificationId): bool {
        $conn = DBConnection::getInstance()->getConnection();
        $sql = "UPDATE Notification SET IsDeleted = 1 WHERE ID = ?";
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
        $sql = "SELECT * FROM Notification WHERE ReceiverID = ? AND Sent = 0 AND IsDeleted = 0 ORDER BY CreatedAt DESC";
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

    public static function getNotificationById(int $id): ?Notification {
        $conn = DBConnection::getInstance()->getConnection();
        $stmt = $conn->prepare("
            SELECT ID, ReceiverID, Message, Sent
            FROM Notification
            WHERE ID = ? AND IsDeleted = 0
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($row = $result->fetch_assoc()) {
            $notification = new Notification($row['ReceiverID'], $row['Message']);
            $notification->setId($row['ID']);
            $notification->setSent((bool) $row['Sent']);
            return $notification;
        }
        
        return null; 
    }
    
    
}


?>