<?php
require_once "IEventParticipationState.php";
require_once 'Event-Participation.php';

class ParticipationDoneState implements IEventParticipationState{
    public function ProsscingParticipation(EventParticipation $eventParticipation): void{
       $eventParticipation->completeParticipation();
       $eventParticipation->updateVolunteerHours($eventParticipation->getVolunteerID(), $eventParticipation->getParticipantHours());
       $eventParticipation->GenerateCertificate();
    }

    public function NextState(EventParticipation $eventParticipation): void{

    }

}
?>