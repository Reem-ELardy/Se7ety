<?php

interface IJSON {
    public function exportToJson(): bool ;
    public function saveToJson(array $data, string $filePath): bool;
}


?>