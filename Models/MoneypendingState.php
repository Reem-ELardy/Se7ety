<?php
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';
require_once "IMoneyDonationState.php";
require_once 'MoneyDonation.php';
require_once 'MoneyDoneState.php';
class MoneypendingState implements IMoneyDonationState{
    public function ProsscingDonation(MoneyDonation $donation): void{
        $donation->updateCashAmount();
    }

    public function NextState(MoneyDonation $donation): void{
        $donation->SetState(new MoneyDoneState());
    }

}
?>