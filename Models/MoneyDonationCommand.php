<?php
require_once 'ICommand.php';
require_once 'MoneyDonation.php';
class MeoneyDonationCommand implements ICommand{
    private MedicalDonation $donation;

    public function __construct(MoneyDonation $donation){
        $this->donation= $donation;
    }

    public Function undo(){
        $this->donation->CancelDonation();
    }
    public Function redo(){

    }

}
?>