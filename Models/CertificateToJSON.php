<?php

require_once 'IJSON.php';

class CertificateToJSON implements IJSON {

    private Certificate $certificate;

    public function __construct(Certificate $certificate) {
        $this->certificate = $certificate;
    }

    public function exportToJson(): bool {

        $filePath = __DIR__ . "/certificates/{$this->certificate->getVolunteerName()}_{$this->certificate->getEventName()}_certificate.json";
    
        $data = [
            'CertificateID' => $this->certificate->getID(),
            'EventName' => $this->certificate->getEventName(),
            'EventDate' =>  $this->certificate->getEventDate()->format('Y-m-d'),
            'VolunteerName' => $this->certificate->getVolunteerName(),
            'VolunteerID' => $this->certificate->getvolunteerID(),
            'EventID' => $this->certificate->geteventID(),
        ];
    
        return $this->saveToJson($data, $filePath);
    }
    
    public function saveToJson(array $data, string $filePath): bool {
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);
    
        if (!is_dir(__DIR__ . '/certificates')) {
            mkdir(__DIR__ . '/certificates', 0777, true); 
        }
    
        file_put_contents($filePath, $jsonData);
        return true;
    }
    
}



?>