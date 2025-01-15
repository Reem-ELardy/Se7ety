<?php

class SMS implements ICommunicationStrategy{

    function send_communication(string $message, Person $person, Subject $event) {
            $recipientPhone = $person->getPhone(); 
            if (!$recipientPhone) {
                return false;
            }  
        $communication = new Communication($this , $message ,$event, $person, MessageType::SMS);
        if ($communication->createCommunication()) {
            return true;
        } else {
            return false;
        }
     }
}


?>