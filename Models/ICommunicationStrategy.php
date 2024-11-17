<?php

interface ICommunicationStrategy {
    public function send_communication(string $message, Person $recipient , Subject $event);
}
?>