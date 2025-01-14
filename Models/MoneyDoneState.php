<?php
require_once "IMoneyDonationState.php";
require_once 'MoneyDonation.php';
class MoneyDoneState implements IMoneyDonationState{
    public function ProsscingDonation(MoneyDonation $donation): void{
       $donation->setDonationStatus('Done');
       if(!$donation->updateDonation()){
        throw new Exception ("The donation amount must be at least $");
       }
    }

    public function NextState(MoneyDonation $donation): void{

    }

}
?>