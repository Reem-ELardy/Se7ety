<?php
require_once 'IDonationPaymentStrategy.php';
require_once 'EWalletDonationPayment.php';
require_once 'CardDonationPayment.php';
require_once 'CashDonationPayment.php';
require_once 'Donation.php';

class MoneyDonation extends Donation {
    protected IDonationPaymentStrategy $donationMethod;
    private float $minAmount = 10.0;
    private $cash;

    public function __construct($DonateID = 0 , $donationtype = 'Money', $cashamount = 0, $status = 'Pending', $isDeleted = false,) {
        parent::__construct(donateID: $DonateID, donationtype:$donationtype,status:$status);
        $this->dbProxy = new DBProxy('user');
        $this->cash=$cashamount;
    }

    public function getCashAmount(): ?float {
        return $this->cash;
    }

    public function setCashAmount($cashamount): void {
            $this->cash = $cashamount;
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

}
?>