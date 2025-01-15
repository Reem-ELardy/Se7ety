<?php

require_once 'IPatientNeedState.php';

class PatientNeedAcceptedState implements IPatientNeedState {
    public function handleRequest(PatientNeed $patientNeed, DonationAdmin $admin): void {
        $patientNeed->setState(new PatientNeedDoneState());
        $patientNeed->setStatus(Status::Done); 
        $patientNeed->updatePatientNeed(); 
    }

    public function NextState(PatientNeed $patientNeed): void {
        $patientNeed->setState(new PatientNeedDoneState());
        $patientNeed->setStatus(Status::Done);
        $patientNeed->updatePatientNeed();
    }
}


