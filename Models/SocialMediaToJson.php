<?php

require_once 'IJsonAdapter.php';

class SocialMediaJsonAdapter implements IJsonAdapter {
    private SocialMedia $socialMedia;

    public function __construct(SocialMedia $socialMedia) {
        $this->socialMedia = $socialMedia;
    }

    public function sendJson(string $message, Person $person, Subject $event): bool {
        $jsonMessage = json_encode([
            'platform' => $this->socialMedia->getPlatform()->value,
            'recipient' => $person->getEmail(),
            'event' => $event->getName(),
            'message' => $message,
        ]);
        return $this->socialMedia->send_communication($jsonMessage, $person, $event);
    }
}


?>

