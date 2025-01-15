<?php

require_once 'Event-Participation.php';

interface IEventParticipationState {
    public function ProsscingParticipation(EventParticipation $eventParticipation): void;
    public function NextState(EventParticipation $eventParticipation): void;
}



?>