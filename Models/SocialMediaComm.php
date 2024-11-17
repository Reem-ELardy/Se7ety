<?php

class SocialMedia implements ICommunicationStrategy {
    protected String $username;
    protected String $platform;

    public function __construct($username, $platform) {
        $this->username = $username;
        $this->platform = $platform;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getPlatform() {
        return $this->platform;
    }

    public function setPlatform($platform) {
        $this->platform = $platform;
    }

    public function send_communication(string $message, Person $person, Subject $event) { 
        $communication = new Communication( $this, $message , $event, $person, MessageType::SocialMedia);
        if ($communication->createCommunication()) {
            return true;
        } else {
            return false;
        }
     }
  
}

?>
