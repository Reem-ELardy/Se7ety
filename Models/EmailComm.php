<?php

class Email implements ICommunicationStrategy{

 public function send_communication(string $message, Person $person) { 
    $communication = new Communication( $this, $message ,$event, $person, MessageType::Email);
    if ($communication->createCommunication()) {
        return true;
    } else {
        return false;
    }
 }
}

?>