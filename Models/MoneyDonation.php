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

    public function __construct($DonateID = 0 , $cashamount = 0, $status = 'Pending', $isDeleted = false, $donationtype = 'Money') {
        parent::__construct(donateID: $DonateID, donationtype:$donationtype,status:$status);
        $this->dbProxy = new DBProxy('user');
        $this->cash=$cashamount;
        $this->state = new MoneypendingState(); 
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

    public function validate($data): bool {
        if ($data < $this->minAmount) {
            throw new Exception("The donation amount must be at least $" . $this->minAmount);
            return false;
        }
        return true;
    }

    public function createMoneyDonation(){
        if($this->validate($this->cash)){
            return $this->createDonation();
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
    public function payment($paymentMethod, $PaymentDetails, $details){
        if($paymentMethod == 'Cash'){
            $this->donationMethod = new CashDonationPayment();
        }elseif($paymentMethod == 'Card'){
            $this->donationMethod = new CardDonationPayment($PaymentDetails);
        }elseif($paymentMethod == 'Ewallet'){
            $this->donationMethod = new EWalletDonationPayment($PaymentDetails);
        }

        return $this->donationMethod->processPayment($details);
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
    public function calculatePayment($paymentMethod, $details, $donationSessionID){
        if(strtolower($paymentMethod) == 'cash'){
            $this->donationMethod = new CashDonationPayment();
        }elseif(strtolower($paymentMethod) == 'card'){
            $this->donationMethod = new CardDonationPayment();
        }elseif(strtolower($paymentMethod) == 'ewallet'){
            $this->donationMethod = new EWalletDonationPayment();
        }

        $totaldata = $this->donationMethod->calculations($details);
        $this->saveState('Totaldata', $totaldata, $donationSessionID);
    }
}
?>