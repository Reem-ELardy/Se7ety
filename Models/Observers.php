<?php


interface Observer {
    public function getId();
    public function update(int $id, string $name, int $locationId, DateTime $date_time, string $description);
}

?>


