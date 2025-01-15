<?php

require_once 'IPatientNeedState.php';

class PatientNeedWaitingState implements IPatientNeedState {
    public function handleRequest(PatientNeed $patientNeed, DonationAdmin $admin): void {
        // Transition to Accepted state
        $patientNeed->setStatus(Status::Accepted);
        $patientNeed->updatePatientNeed(); // Save changes to the database
    }

    public function NextState(PatientNeed $patientNeed): void {
        $patientNeed->setStatus(Status::Accepted); // Transition to the next state
    }
}
?>
