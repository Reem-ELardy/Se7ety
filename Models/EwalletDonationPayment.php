<?php
class EWalletDonationPayment implements IDonationPaymentStrategy {
    private $transactionID;
    private float $EwalletTax = 0.2;


    public function __construct($transactionID = 0) {
        $this->transactionID = $transactionID;
    }

    public function calculations($details){
        $data = [
            'Tax' => $details * $this->EwalletTax, 
            'Total Price' => $details + ($details * $this->EwalletTax)
        ];

        return $data;
    }

    public function processPayment($details){
        return ($details + $details * $this->EwalletTax);
    }

    public function getType() {
        return 'Ewallet';
    }
}

?>