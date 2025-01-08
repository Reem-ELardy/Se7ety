<?php
require_once "IMoneyDonationState.php";
require_once 'MoneyDonation.php';
class MoneyDoneState implements IMoneyDonationState{
    public function ProsscingDonation(MoneyDonation $donation): void{
       if( $donation->updateDonation()){
        }
    }

    public function NextState(MoneyDonation $donation): void{

    }

}
?>