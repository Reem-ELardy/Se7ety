<?php
require_once 'Person-Model.php';
require_once 'Event.php';
require_once 'EmailComm.php';
require_once 'Receipt.php';
require_once 'Certificate.php';
require_once 'ICommunicationStrategy.php';
class CommunicationFacade {
    private Email $email;
    private Person $person;
    private ?Subject $event;
    private ?Receipt $receipt;
    private ?Certificate $certificate;

    public function __construct(Email $email, Person $person, ?Subject $event = null, ?Receipt $receipt = null, ?Certificate $certificate = null) {
        $this->email =$email;
        $this->person = $person; 
        $this->event = $event;
        $this->receipt = $receipt;
        $this->certificate = $certificate;
    }

    private function prepareEmailDetails(string $to, string $subject, string $message): array {
        return [
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
        ];
    }

    private function simulateEmail(array $emailDetails): bool {
        // Log email details to a file for testing
        $logFile = __DIR__ . '/email_log.txt';
        $logContent = "To: " . $emailDetails['to'] . "\n" .
                      "Subject: " . $emailDetails['subject'] . "\n" .
                      "Message:\n" . $emailDetails['message'] . "\n" .
                      "----------------------------------------\n";

        file_put_contents($logFile, $logContent, FILE_APPEND);

        // Simulate email was sent successfully
        return true;
    }
    public function sendSignupThankYou(): bool {
        if ($this->person) {
            $message = "Thank you for joining our website!";
               // Create a temporary event instance for the signup message
            $signupEvent = new Event(
                id: 1,
                name: "Signup Event",
                locationID: 0,
                date_time: new DateTime(),
                description: "Welcome signup event",
                max_no_of_attendance: 0,
                type: EventType::Other
            );
            $emailDetails = $this->prepareEmailDetails(
                $this->person->getEmail(),
                "Welcome to Our Website!",
                $message
            );
            $this->simulateEmail($emailDetails);
            return $this->email->send_communication($message, $this->person, $signupEvent);
        }
        echo "Error: Person is not set.";
        return false;
    }
    public function sendEventParticipationThankYou(): bool {
        if ($this->person && $this->event) {
            $message = "Thank you for participating in the event!";
            $emailDetails = $this->prepareEmailDetails(
                $this->person->getEmail(),
                "Notification for " . $this->event->getName(),
                $message
            );
            $this->simulateEmail($emailDetails);
            return $this->email->send_communication($message, $this->person, $this->event);
        }
        echo "Error: Person or Event is not set.";
        return false;
    }


    public function sendReceipt(): bool {
        if ($this->receipt && $this->person instanceof Donor) {
            $receiptContent = $this->receipt->generate_receipt();
            $message = "Your Receipt from Se7ety:\n" . $receiptContent;
        if (!($this->person instanceof Donor)) {
            echo "Error: The person is not a donor.";
            return false;
        }
            $emailDetails = $this->prepareEmailDetails(
                $this->person->getEmail(),
                "Your Receipt from Se7ety",
                $message
            );
            $this->simulateEmail($emailDetails);
            // Send the communication using the donor's email
            return $this->email->send_communication($message, $this->person, new Event(
                id: 0,
                name: "Donation Receipt",
                locationID: 0,
                date_time: new DateTime(),
                description: "Thank you for your generous donation.",
                max_no_of_attendance: 0,
                type: EventType::Other
            ));
        }
        echo "Error: Receipt is not set.";
        return false;
    }

    public function sendCertificate(): bool {
        if ($this->certificate && $this->event) {
            $certificateContent = $this->certificate->generateCertificateContent();
        $emailDetails = $this->prepareEmailDetails(
            $this->person->getEmail(),
            "Your Certificate from Se7ety",
            $certificateContent
        );
        $this->simulateEmail($emailDetails);
        return $this->email->send_communication($certificateContent, $this->person, $this->event);
    }

    echo "Error: Certificate or Event is not set.";
    return false;
}
      
}

?>
