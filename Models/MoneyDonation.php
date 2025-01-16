<?php
require_once 'IDonationPaymentStrategy.php';
require_once 'EWalletDonationPayment.php';
require_once 'CardDonationPayment.php';
require_once 'CashDonationPayment.php';
require_once 'Donation.php';
require_once 'IMoneyDonationState.php';
require_once 'MoneypendingState.php';

class MoneyDonation extends Donation {
    protected IDonationPaymentStrategy $donationMethod;
    private float $minAmount = 10.0;
    private $cash;
    protected IMoneyDonationState $state;


    public function __construct($DonateID = 0 , $cashamount = 0, $DonationID = 0, $status = 'Pending', $isDeleted = false, $donationtype = 'Money') {
        parent::__construct(donateID: $DonateID, donationtype:$donationtype, status:$status, id: $DonationID);
        $this->state = new MoneypendingState();
        $this->cash=$cashamount;
    }

    public function getCashAmount(): ?float {
        return $this->cash;
    }

    public function setCashAmount($cashamount): void {
        $this->cash = $cashamount;
    }

    public function SetState($state){
        $this->state=$state;
    }

    public function setPaymentMethod($paymentMethod = null){
        if(strtolower($paymentMethod) == 'cash'){
            $this->donationMethod = new CashDonationPayment();
        }elseif(strtolower($paymentMethod) == 'card'){
            $this->donationMethod = new CardDonationPayment($paymentMethod );
        }elseif(strtolower($paymentMethod) == 'ewallet'){
            $this->donationMethod = new EWalletDonationPayment($paymentMethod );
        }
    }

    public function getPaymentMethod() {
        if ($this->donationMethod) {
            return $this->donationMethod->getType();
        }
        return null;
    }

    public function validate(): bool {
        if ($this->cash < $this->minAmount) {
            return false;
        }
        return true;
    }

    public function createMoneyDonation(){
        if($this->validate($this->cash)){
            return $this->updateCashAmount();
        }
        return false;
    }

    public function updateCashAmount(){
        $query = "UPDATE DONATION Set CashAmount = ? WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->cash , $this->getDonationId()]);

        if ($stmt) {
            return true;
        }
        return false;
    }

    //Payment using Payment Strategy
    public function payment(){
        return $this->donationMethod->processPayment($this->cash);
    }


    /*State Function for State DP*/
    public function ProcessDonation(): void{
        $this->state->ProsscingDonation($this);
    }

    public function CompleteDonation(){
        $this->state->NextState($this);
        $this->ProcessDonation(); 
    }

    //Template Function
    public function calculatePayment(){
        $totaldata = $this->donationMethod->calculations($this->cash);
        return $totaldata;
    }

}
?>