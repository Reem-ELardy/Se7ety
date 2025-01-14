<?php

require_once 'IPatientNeedState.php';

class PatientNeedDoneState implements IPatientNeedState {
    public function handleRequest(PatientNeed $patientNeed, DonationAdmin $admin): void {
        echo "Handling request in Done State for PatientNeed (PatientID: {$patientNeed->getPatientID()})...\n";
        echo "Request is already completed. No further action required.\n";
    }

    public function progressState(PatientNeed $patientNeed): void {
        echo "Request is already in Done State. Cannot progress further.\n";
    }
}
