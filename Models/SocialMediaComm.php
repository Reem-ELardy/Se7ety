<?php

require_once 'ICommunicationStrategy.php';
require_once 'Communication.php';


  enum PlatformType: string {
    case Facebook = "Facebook";
    case Instagram = "Instagram";
    case Twitter = "Twitter";
}

class SocialMedia implements ICommunicationStrategy {
    private PlatformType $platform;
    private string $email;
    public function __construct(PlatformType $platform, string $email) {
        $this->platform = $platform;
        $this->email = $email;
    }

    public function getPlatform(): PlatformType {
        return $this->platform;
    }

    public function setPlatform(PlatformType $platform): void {
        $this->platform = $platform;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function send_communication(string $message, Person $person, Subject $event): bool {
        
        //$this->simulateSocialMediaMessage($message, $person, $event);
        $communication = new Communication($this, $message, $event, $person, MessageType::SocialMedia);
        return $communication->createCommunication();
    }

    // // Simulate sending a message via social media
    // private function simulateSocialMediaMessage(string $message, Person $person, Subject $event): void {
    //     $logFile = __DIR__ . '/social_media_log.txt';
    //     $logContent = "Platform: " . $this->platform->value . "\n" .
    //                   "Email: " . $this->email . "\n" .
    //                   "To: " . $person->getEmail() . "\n" .
    //                   "Message:\n" . $message . "\n" .
    //                   "Related Event: " . $event->getName() . "\n" .
    //                   "----------------------------------------\n";

    //     if (!is_dir(__DIR__ . '/social_media_logs')) {
    //         mkdir(__DIR__ . '/social_media_logs', 0777, true); // Ensure the directory exists
    //     }

    //     file_put_contents($logFile, $logContent, FILE_APPEND);
    // }
}

?>
