<?php

require_once 'IPatientNeedState.php';

class PatientNeedAcceptedState implements IPatientNeedState {
    public function handleRequest(PatientNeed $patientNeed, DonationAdmin $admin): void {
       
    }

    public function NextState(PatientNeed $patientNeed): void {
       
    }
}
