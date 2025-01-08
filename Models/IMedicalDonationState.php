<?php

require_once 'MedicalDonation.php';

interface IMedicalDonationState {
    public function ProsscingDonation(MedicalDonation $donation): void;
    public function NextState(MedicalDonation $donation): void;
}





?>