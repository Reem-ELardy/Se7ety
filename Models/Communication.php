<?php

require_once 'ICommunicationStrategy.php';
require_once 'Event.php';
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';

enum MessageType: string {
    case Email = 'E-Mail';
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
    protected $dbProxy;

    public function __construct(ICommunicationStrategy $communicationMethod, $message, Subject  $event , Person $recipient , MessageType $messageType) {
        $this->dbProxy = new DBProxy('user');
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
        $details = "Event Name: " . $event->getName() .
                   ", Location ID: " . $event->getLocationID() .
                   ", Date & Time: " . $event->getDateTime()->format('Y-m-d H:i:s') .
                   ", Description: " . $event->getDescription();
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
                $this->communicationMethod = new SocialMedia($platform ?? PlatformType::Facebook, $this->recipient->getEmail());
                break;
             }
            $this->communicationMethod->send_communication($message, $this -> recipient, $this->event);
    }

    public function createCommunication(): bool {
    
        if ($this->recipient->getId() === null) {
                return false; 
        }
        $query = "INSERT INTO Communication (PersonID, Type, Message) VALUES (?, ?, ?)";
        $stmt = $this->dbProxy->prepare($query, [$this->recipient->getId(),$this->messageType->value, $this->message]);
    
        if (!$stmt) {
            return false;
        }
        $this->id = $this->dbProxy->getInsertId();
        return true;

    }

    public function updateCommunication(): bool {
        if ($this->id === null) {
            return false;
        }
        $query = "UPDATE Communication SET PersonID = ?, Type = ?, Message = ? WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query,  [ $this->recipient->getId(), $this->messageType->value,$this->message,$this->id]);
        if (!$stmt) {
            return false;
        }
    
        return true;
    }

    public function readCommunication(int $communicationId): bool {
        $query = "SELECT Communication.ID, Communication.PersonID, Communication.Type, Communication.Message, 
                         Person.Name
                  FROM Communication
                  INNER JOIN Person ON Communication.PersonID = Person.ID
                  WHERE Communication.ID = ?";
        
        $stmt = $this->dbProxy->prepare($query,[$communicationId]);
        if (!$stmt) {
            return false;
        }
    
        $id = null;
        $personId = null;
        $type = null;
        $message = null;
        $personName = null;
    
        $stmt->bind_result($id, $personId, $type, $message, $personName);
    
        if ($stmt->fetch()) {
            $this->id = $id;
            $this->recipient = new Person($personName); 
            $this->messageType = MessageType::from($type);
            $this->message = $message;
    
            return true;
        } else {
            return false;
        }
    }  

    public function deleteCommunication(): bool {
        if ($this->id === null) {
            return false;
        }
    
        $query = "UPDATE Communication SET IsDeleted = true WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->id]);

    if (!$stmt) {
        return false;
    }
    return true;
}
   

}

?>
