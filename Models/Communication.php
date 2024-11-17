<?php

require_once 'ICommunicationStrategy.php';
require_once 'Event.php';

enum MessageType: string {
    case Email = 'Email';
    case SMS = 'SMS';
    case SocialMedia = 'SocialMedia';
}

class Communication {
    private $id;
    private Person $recipient;
    private String $message;
    private Subject $event;
    private ICommunicationStrategy $communicationMethod;
    private MessageType $messageType; 

    public function __construct(ICommunicationStrategy $communicationMethod, $message, Subject  $event , Person $recipient , MessageType $messageType) {
        $this->communicationMethod = $communicationMethod;
        $this->recipient = $recipient;
        $this->message = $message;
        $this->event = $event;
        $this->messageType = $messageType;
    }
    public function getId() {
        return $this->id;
    }
    
    public function setId($id) {
        $this->id = $id;
    }
    public function getRecipient() {
        return $this->recipient;
    }

    public function setRecipient($recipient) {
        $this->recipient = $recipient;
    }
    
    public function getMessage() {
        return $this->message;
    }

    public function setMessage($message) {
        $this->message = $message;
    }

    public function getEvent() {
        return $this->event;
    }

    public function setEvent($event) {
        $this->event = $event;
    }

    public function getCommunicationMethod() {
        return $this->communicationMethod;
    }

    public function setCommunicationMethod(ICommunicationStrategy $communicationMethod) {
        $this->communicationMethod = $communicationMethod;
    }
    public function getMessageType(): MessageType {
        return $this->messageType;
    }

    public function setMessageType(MessageType $messageType) {
        $this->messageType = $messageType;
    }

    public function update(string $name, string $location, DateTime $date_time, string $description){
        $this->message = "Event Update: $name at $location on " . $date_time->format('Y-m-d H:i:s') . 
                         ". Description: $description.";
    }

    public function communicationDisplay(Event $event): void {
        // Assuming $event has methods to get relevant details
        $details = "Event Name: " . $event->getName() .
                   ", Location ID: " . $event->getLocationID() .
                   ", Date & Time: " . $event->getDateTime()->format('Y-m-d H:i:s') .
                   ", Description: " . $event->getDescription();
        echo "Communication Details: " . $details;
    }
    public function send_communication(MessageType $type,string $message) {
        switch ($type) {
            case MessageType::Email:
                $this->communicationMethod= new Email();
                break;
            case MessageType::SMS:
                $this->communicationMethod = new SMS();
                break;
            case MessageType::SocialMedia:
                    $this->communicationMethod = new SocialMedia($username ?? 'defaultUser', $platform ?? 'defaultPlatform');
                break;
             }
            $this->communicationMethod->send_communication($message, $this -> recipient, $this->event);
    }

    public function createCommunication() {
        $conn = DBConnection::getInstance()->getConnection();
    
        if ($this->recipient->getId() === null) {
                return false; // Stop if Person IS NOT EXIST
        }
        $query = "INSERT INTO Communication (PersonID, Type, Message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
    
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
    
        $personId = $this->recipient->getId(); 
        $type = $this->messageType->value; 
        $message = $this->message;

        $stmt->bind_param("iss", $personId, $type, $message);
        $result = $stmt->execute();
    
        if ($result) {
            $this->id = $conn->insert_id;
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
    
        $stmt->close();
        return $result;
    }

    public function updateCommunication() {
        $conn = DBConnection::getInstance()->getConnection();
        $query = "UPDATE Communication SET PersonID = ?, Type = ?, Message = ? WHERE ID = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
        $type = $this->messageType->value; // Convert enum to string
        $stmt->bind_param("issii", $this->recipient->getId(), $type, $this->message, $this->id);
        $result = $stmt->execute();
    
        if (!$result) {
            echo "Execute failed: " . $stmt->error;
            return false;
        }
    
        return true;
    }

    public function readCommunication($communicationId) {
        $conn = DBConnection::getInstance()->getConnection();
        $query = "SELECT Communication.ID, Communication.PersonID, Communication.Type, Communication.Message, 
                         Person.Name
                  FROM Communication
                  INNER JOIN Person ON Communication.PersonID = Person.ID
                  WHERE Communication.ID = ?";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $communicationId);
        $stmt->execute();
        $stmt->bind_result($this->id, $personId, $type, $message, $personName);
        if ($stmt->fetch()) {
            
            $this->recipient = new Person($personName); 
            $this->messageType = MessageType::from($type); 
            $this->message = $message;
    
            return true;
        } else {
            echo "Communication not found.\n";
            return false;
        }
    }

    public function deleteCommunication($communicationId) {
        $conn = DBConnection::getInstance()->getConnection();
    
        if ($communicationId === null) {
            return false;
        }
        $query = "UPDATE Communication SET IsDeleted = true WHERE ID = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("i", $communicationId);
    $result = $stmt->execute();

    if (!$result) {
        echo "Execute failed: " . $stmt->error;
    } else {
        echo "Communication with ID " . $communicationId . " marked as deleted.\n";
    }

    return $result;
}
   

}

?>
