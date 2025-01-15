<?php
require_once 'Person-Model.php';
require_once 'Event.php';
require_once 'EmailComm.php';
require_once 'Receipt.php';
require_once 'Certificate.php';
require_once 'ICommunicationStrategy.php';
require_once 'Ticket.php';
require_once 'SMSComm.php';
require_once 'Volunteer-Model (1).php';
require_once 'Patient-Model.php';

class CommunicationFacade {
    private ?Email $email;
    private ?SMS $sms;
    private ?SocialMedia $socialMedia;
    private Person $person;
    private ?Subject $event;
    private ?Receipt $receipt;
    private ?Certificate $certificate;
    private ?Ticket $ticket;


    public function __construct(?Email $email = null,?SMS $sms = null,?SocialMedia $socialMedia = null, Person $person, ?Subject $event = null, ?Receipt $receipt = null, ?Certificate $certificate = null, ?Ticket $ticket = null) {
        $this->email =$email;
        $this->sms = $sms;
        $this->socialMedia = $socialMedia;
        $this->person = $person; 
        $this->event = $event;
        $this->receipt = $receipt;
        $this->certificate = $certificate;
        $this->ticket = $ticket;
    }
    private function sendCommunication(string $message, string $type): bool {
        $emailResult = true;
        $smsResult = true;
        $socialresult = true;
        if ($type === "email" || $type === "both") {
            $emailDetails = $this->prepareEmailDetails(
                $this->person->getEmail(),
                "Notification",
                $message
            );
            $this->simulateEmail($emailDetails);
            $emailResult = $this->email->send_communication($message, $this->person, $this->event);
        }
    
        if ($type === "sms" || $type === "both") {
            $this->simulateSMS($message, $this->person->getPhone());
            $smsResult = $this->sms->send_communication($message, $this->person, $this->event);
        }
        if ($type === "social_media" && $this->socialMedia) {
            $this->simulateSocial($message, $this->socialMedia->getPlatform(), $this->person->getEmail());
            $socialresult = $this->socialMedia->send_communication($message, $this->person, $this->event);
        }
    
        return $emailResult && $smsResult && $socialresult;

    }
    private function prepareEmailDetails(string $to, string $subject, string $message): array {
        return [
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
        ];
    }

    // private function simulateEmail(array $emailDetails): bool {
    //     // Log email details to a file for testing
    //     $logFile = __DIR__ . '/email_log.txt';
    //     $logContent = "To: " . $emailDetails['to'] . "\n" .
    //                   "Subject: " . $emailDetails['subject'] . "\n" .
    //                   "Message:\n" . $emailDetails['message'] . "\n" .
    //                   "----------------------------------------\n";

    //     file_put_contents($logFile, $logContent, FILE_APPEND);

    //     // Simulate email was sent successfully
    //     return true;
    // }
    private function simulateEmail(array $emailDetails): bool {
        $userEmail = str_replace('@', '_', $emailDetails['to']);
        $userEmail = str_replace('.', '_', $userEmail); 
        $logFile = __DIR__ . "/email_logs/{$userEmail}_email_log.txt";
    
        $logContent = "To: " . $emailDetails['to'] . "\n" .
                      "Subject: " . $emailDetails['subject'] . "\n" .
                      "Message:\n" . $emailDetails['message'] . "\n" .
                      "----------------------------------------\n";
    
        if (!is_dir(__DIR__ . '/email_logs')) {
            mkdir(__DIR__ . '/email_logs', 0777, true); 
        }
    
        file_put_contents($logFile, $logContent, FILE_APPEND);
        return true;
    }
     
    // private function simulateSMS(string $message, string $phone): bool {
    //     $logFile = __DIR__ . '/sms_log.txt';
    //     $logContent = "To: " . $phone . "\n" .
    //                   "Message:\n" . $message . "\n" .
    //                   "----------------------------------------\n";

    //     file_put_contents($logFile, $logContent, FILE_APPEND);

    //     return true;
    // }
    // public function sendSignupThankYou(string $type = "both"): bool {
    //     if ($this->person) {
    //         $message = "Thank you for joining our website!";
    //         return $this->sendcommunication($message, $type);
    //     }
    //     echo "Error: Person is not set.";
    //     return false;
    // }
    private function simulateSMS(string $message, string $phone): bool {
        $phoneSanitized = preg_replace('/\D/', '', $phone); 
        $logFile = __DIR__ . "/sms_logs/{$phoneSanitized}_sms_log.txt";
    
        $logContent = "To: " . $phone . "\n" .
                      "Message:\n" . $message . "\n" .
                      "----------------------------------------\n";
    
        if (!is_dir(__DIR__ . '/sms_logs')) {
            mkdir(__DIR__ . '/sms_logs', 0777, true); 
        }
    
        file_put_contents($logFile, $logContent, FILE_APPEND);
    
        return true;
    }
    private function simulateSocial(string $message, PlatformType $platform, string $email): void {
        $logFile = __DIR__ . "/social_media_logs/{$platform->value}_social_log.txt";
    
        $logContent = "Platform: " . $platform->value . "\n" .
                      "Email: " . $email . "\n" .
                      "Message:\n" . $message . "\n" .
                      "----------------------------------------\n";
    
        if (!is_dir(__DIR__ . '/social_media_logs')) {
            mkdir(__DIR__ . '/social_media_logs', 0777, true);
        }
    
        file_put_contents($logFile, $logContent, FILE_APPEND);
    }
    
    public function sendEventParticipationThankYou(string $type = "both"): bool {
        if ($this->person && $this->event) {
            $message = "Thank you for participating in the event!";
            return $this->sendCommunication($message, $type);
        }
        return false;
    }


    public function sendReceipt(string $type = "email"): bool {
        if ($this->receipt && $this->person instanceof Donor) {
            $receiptContent = $this->receipt->generate_receipt();
            $message = "Your Receipt from Se7ety:\n" . $receiptContent;
            return $this->sendCommunication($message, $type);
        }
        // if (!($this->person instanceof Donor)) {
        //     echo "Error: The person is not a donor.";
        //     return false;
        // }
        //     $emailDetails = $this->prepareEmailDetails(
        //         $this->person->getEmail(),
        //         "Your Receipt from Se7ety",
        //         $message
        //     );
        //     $this->simulateEmail($emailDetails);
        //     // Send the communication using the donor's email
        //     return $this->email->send_communication($message, $this->person, new Event(
        //         id: 0,
        //         name: "Donation Receipt",
        //         locationID: 0,
        //         date_time: new DateTime(),
        //         description: "Thank you for your generous donation.",
        //         max_no_of_attendance: 0,
        //         type: EventType::Other
        //     ));
        // }
        return false;
    }

    public function sendCertificate(string $type = "email"): bool {
        if ($this->certificate && $this->event) {
            $certificateContent = $this->certificate->generateCertificateContent();
            return $this->sendCommunication($certificateContent, $type);
        }
        return false;
    }
    public function sendTicketSMS(): bool {
        if ($this->ticket) {
            $patientName = $this->ticket->getPatientName();
            $eventName = $this->ticket->getEventName();
            $eventDate = $this->ticket->getDateTime()->format('Y-m-d H:i:s');
            $patientPhone = $this->person->getPhone();
            if (empty($patientPhone)) {
                return false;
            }
            $ticketMessage = "Dear $patientName, your ticket for the event $eventName has been confirmed. Event Date: $eventDate";
            return $this->sendCommunication($ticketMessage, "sms");
        }
        

        return false;
    }
    public function sendEventArticle(string $type): bool {
        if (!$this->event) {

            return false;
        }
    
        $article = $this->generateEventArticleList();
        $recipients = $this->getRecipients(); // Fetch volunteers and patients
        $success = true;
    
        foreach ($recipients as $recipient) {
            $this->person = $recipient; // Set the current recipient
            $result = $this->sendCommunication($article, $type);
            $success = $success && $result; // Combine results to check overall success
        }
    
        return $success;
    }
    private function generateEventArticleList(): string {
        $eventsModel = new EventsModel();
        $events = $eventsModel->getUpcomingEvents();
    
        if (empty($events)) {
            return "No upcoming events available at this time.";
        }
    
        $article = "Upcoming Events:\n";
        foreach ($events as $event) {
            $article .= "----------------------------------------\n";
            $article .= "Name: " . $event->getName() . "\n";
            $article .= "Description: " . $event->getDescription() . "\n";
            $article .= "Date: " . $event->getDateTime()->format('Y-m-d H:i:s') . "\n";
            $article .= "Location ID: " . $event->getLocationID() . "\n";
            $article .= "----------------------------------------\n";
        }
    
        return $article;
    }
    private function getRecipients(): array {
        $recipients = [];
    
        // Fetch volunteers
        $volunteersModel = new Volunteer();
        $volunteers = $volunteersModel->getAllVolunteers();
        $recipients = array_merge($recipients, $volunteers);
    
        // Fetch patients
        $patientsModel = new Patient();
        $patients = $patientsModel->getAllPatients();
        $recipients = array_merge($recipients, $patients);
    
        return $recipients;
    }
    
 
      
}

?>
