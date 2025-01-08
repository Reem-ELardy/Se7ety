<?php

require_once 'MoneyDonation.php';

interface IMoneyDonationState {
    public function ProsscingDonation(MoneyDonation $donation): void;
    public function NextState(MoneyDonation $donation): void;
}



?>