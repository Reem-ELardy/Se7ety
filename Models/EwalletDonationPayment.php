<?php
class EWalletDonationPayment implements IDonationPaymentStrategy {
    private int $transactionID;
    private float $EwalletTax = 0.2;


    public function __construct($transactionID = 0) {
        $this->transactionID = $transactionID;
    }

    public function calculations($details){
        $data = [
            'Tax' => $this->EwalletTax, 
            'Total Price' => $details + ($details * $this->EwalletTax)
        ];

        return $data;
    }

    public function processPayment($details){
        return ($details + $details * $this->EwalletTax);
    }
}

?>