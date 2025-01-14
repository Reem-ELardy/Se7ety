<?php

require_once 'IPatientNeedState.php';

class PatientNeedWaitingState implements IPatientNeedState {
    public function handleRequest(PatientNeed $patientNeed, DonationAdmin $admin): void {
        echo "Handling request in Waiting State for PatientNeed (PatientID: {$patientNeed->getPatientID()})...\n";

        if ($admin->isMedicineAvailable($patientNeed->getMedicalID())) {
            echo "Medicine is available. Moving to Accepted State.\n";
            $patientNeed->setState(new PatientNeedAcceptedState());
            $patientNeed->setStatus(Status::Accepted); 
            $patientNeed->updatePatientNeed();
        } else {
            echo "Medicine is not available. Remaining in Waiting State.\n";
        }
    }

    public function progressState(PatientNeed $patientNeed): void {
        echo "Cannot progress from Waiting State without admin action.\n";
    }
}


