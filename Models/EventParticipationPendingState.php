<?php
require_once "IEventParticipationState.php";
require_once 'Event-Participation.php';
require_once 'EventParticipationDoneState.php';


class ParticipationPendingState implements IEventParticipationState{
    public function ProsscingParticipation(EventParticipation $eventParticipation): void{

    }

    public function NextState(EventParticipation $eventParticipation): void{
        $eventParticipation->SetState(new ParticipationDoneState());
    }

}
?>