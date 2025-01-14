<?php

require_once 'IPatientNeedState.php';

class PatientNeedAcceptedState implements IPatientNeedState {
    public function handleRequest(PatientNeed $patientNeed, DonationAdmin $admin): void {
        echo "Handling request in Accepted State for PatientNeed (PatientID: {$patientNeed->getPatientID()})...\n";
        echo "Moving to Done State.\n";
        $patientNeed->setState(new PatientNeedDoneState());
        $patientNeed->setStatus(Status::Done); 
        $patientNeed->updatePatientNeed(); 
    }

    public function progressState(PatientNeed $patientNeed): void {
        echo "Progressing from Accepted State to Done State.\n";
        $patientNeed->setState(new PatientNeedDoneState());
        $patientNeed->setStatus(Status::Done);
        $patientNeed->updatePatientNeed();
    }
}


