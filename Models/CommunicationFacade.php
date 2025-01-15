<?php
require_once 'Person-Model.php';
require_once 'Event.php';
require_once 'EmailComm.php';
require_once 'Receipt.php';
require_once 'Certificate.php';
require_once 'ICommunicationStrategy.php';
require_once 'Ticket.php';
require_once 'SMSComm.php';
class CommunicationFacade {
    private ?Email $email;
    private ?SMS $sms;
    private Person $person;
    private ?Subject $event;
    private ?Receipt $receipt;
    private ?Certificate $certificate;
    private ?Ticket $ticket;

    public function __construct(?Email $email = null,?SMS $sms = null, Person $person, ?Subject $event = null, ?Receipt $receipt = null, ?Certificate $certificate = null, ?Ticket $ticket = null) {
        $this->email =$email;
        $this->sms = $sms;
        $this->person = $person; 
        $this->event = $event;
        $this->receipt = $receipt;
        $this->certificate = $certificate;
        $this->ticket = $ticket;
    }
    private function sendCommunication(string $message, string $type): bool {
        $emailResult = true;
        $smsResult = true;
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
    
        return $emailResult && $smsResult;

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
        $userEmail = str_replace('.', '_', $userEmail); // Sanitize the email for file naming
        $logFile = __DIR__ . "/email_logs/{$userEmail}_email_log.txt";
    
        $logContent = "To: " . $emailDetails['to'] . "\n" .
                      "Subject: " . $emailDetails['subject'] . "\n" .
                      "Message:\n" . $emailDetails['message'] . "\n" .
                      "----------------------------------------\n";
    
        if (!is_dir(__DIR__ . '/email_logs')) {
            mkdir(__DIR__ . '/email_logs', 0777, true); // Ensure the directory exists
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
        $phoneSanitized = preg_replace('/\D/', '', $phone); // Remove non-digit characters
        $logFile = __DIR__ . "/sms_logs/{$phoneSanitized}_sms_log.txt";
    
        $logContent = "To: " . $phone . "\n" .
                      "Message:\n" . $message . "\n" .
                      "----------------------------------------\n";
    
        if (!is_dir(__DIR__ . '/sms_logs')) {
            mkdir(__DIR__ . '/sms_logs', 0777, true); // Ensure the directory exists
        }
    
        file_put_contents($logFile, $logContent, FILE_APPEND);
    
        return true;
    }
    
    public function sendEventParticipationThankYou(string $type = "both"): bool {
        if ($this->person && $this->event) {
            $message = "Thank you for participating in the event!";
            return $this->sendCommunication($message, $type);
        }
        echo "Error: Person or Event is not set.";
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
        echo "Error: Receipt is not set.";
        return false;
    }

    public function sendCertificate(string $type = "email"): bool {
        if ($this->certificate && $this->event) {
            $certificateContent = $this->certificate->generateCertificateContent();
            return $this->sendCommunication($certificateContent, $type);
        }
        echo "Error: Certificate or Event is not set.";
        return false;
    }
    public function sendTicketSMS(): bool {
        if ($this->ticket) {
            $patientName = $this->ticket->getPatientName();
            $eventName = $this->ticket->getEventName();
            $eventDate = $this->ticket->getDateTime()->format('Y-m-d H:i:s');
            $patientPhone = $this->person->getPhone();
            if (empty($patientPhone)) {
                echo "Error: Patient phone number is not set.";
                return false;
            }
            $ticketMessage = "Dear $patientName, your ticket for the event $eventName has been confirmed. Event Date: $eventDate";
            return $this->sendCommunication($ticketMessage, "sms");
        }
        
    
        echo "Error: Ticket, Person, or Event is not set.";
        return false;
    }
    
      
}

?>
