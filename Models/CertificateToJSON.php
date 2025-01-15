<?php

require_once 'IJSON.php';

class CertificateToJSON implements IJSON {
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