<?php

require_once "Observers.php";

interface Subject {
    public function registerObserver(Observer $o);
    public function removeObserver(Observer $o);
    public function notifyObserver();
    public function getId();
    public function getName();
    public function getLocationID();
    public function getDateTime();
    public function getDescription();
}

?>