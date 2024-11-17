<?php

require_once "Event.php";

interface Observer {
    public function update(string $name, string $location, DateTime $date_time, string $description);
}

interface Display {
    public function display();
}

class Notification implements Observer,Display {
    private string $message;

    public function setMessage(string $message): void {
        $this->message = $message;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function update(string $name, string $location, DateTime $date_time, string $description){
        $this->message = "Event Update: $name at $location on " . $date_time->format('Y-m-d H:i:s') . 
                         ". Description: $description.";
    }

    // Method to display communication information related to the event
    public function communicationDisplay(Event $event): void {
        // Assuming $event has methods to get relevant details
        $details = "Event Name: " . $event->getName() .
                   ", Location ID: " . $event->getLocationID() .
                   ", Date & Time: " . $event->getDateTime()->format('Y-m-d H:i:s') .
                   ", Description: " . $event->getDescription();
        echo "Communication Details: " . $details;
    }

    // Method to display the message
    public function display(){
        echo $this->message;
    }
}

class EventReminder implements Observer, Display {
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

    // Method to update the reminder message with new event details
    public function update(string $name, string $location, DateTime $date_time, string $description): void {
        $this->reminderMessage = "Reminder: The event '$name' is scheduled at $location on " . 
                                 $date_time->format('Y-m-d H:i:s') . ". Description: $description.";
    }

    // Method to display communication information related to the event
    public function communicationDisplay(Event $event): void {
        $details = "Event Name: " . $event->getName() .
                   ", Location ID: " . $event->getLocationID() .
                   ", Date & Time: " . $event->getDateTime()->format('Y-m-d H:i:s') .
                   ", Description: " . $event->getDescription();
        echo "Communication Details: " . $details;
    }

    // Method to display the reminder message
    public function display(): void {
        echo $this->reminderMessage;
    }
}

?>


