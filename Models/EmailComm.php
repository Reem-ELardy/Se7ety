<?php
require_once 'ICommunicationStrategy.php';
require_once 'Communication.php';

class Email implements ICommunicationStrategy {

    public function send_communication(string $message, Person $person, Subject $event): bool {
        $communication = new Communication($this, $message, $event, $person, MessageType::Email);
        return $communication->createCommunication();
    }

    }


?>