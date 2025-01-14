<?php
require_once 'ICommand.php';
require_once 'MedicalDonation.php';
class MedicalDonationCommand implements ICommand{
    private MedicalDonation $donation;

    public function __construct(MedicalDonation $donation){
        $this->donation= $donation;
    }

    public Function undo(){
        $this->donation->CancelDonation();
    }
    public Function redo(){

    }

}
?>