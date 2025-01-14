<?php

interface IPatientNeedState {
    public function handleRequest(PatientNeed $patientNeed, DonationAdmin $admin): void;
    public function progressState(PatientNeed $patientNeed): void;
}
?>
