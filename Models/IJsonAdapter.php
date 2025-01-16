<?php

interface IJsonAdapter {
    public function sendJson(string $message, Person $person, Subject $event): bool;
}


?>