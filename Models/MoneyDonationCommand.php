<?php
require_once 'ICommand.php';
require_once 'MoneyDonation.php';
class MeoneyDonationCommand implements ICommand{
    private MoneyDonation $donation;

    public function __construct(MoneyDonation $donation){
        $this->donation= $donation;
    }

    public Function undo(){
        $this->donation->CancelDonation();
    }
    public Function redo(){
        $donation_id1 = uniqid('donation_', true);
        $this->donation->setDonationStatus('Pending');
        $this->donation->processDonationTemplate($donation_id1);
        return [$donation_id1, $this->donation];
    }

}


?>