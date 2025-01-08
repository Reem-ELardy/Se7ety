<?php

class CardDonationPayment implements IDonationPaymentStrategy {
    private $visaNumber;
    private DateTime $expirationDate;
    private $bankName;
    private float $cardTax = 0.3;


    public function __construct($details = null) {
        $this->visaNumber = $details[0] ?? '';
        $this->expirationDate = $details[1] ?? new DateTime();
        $this->bankName = $details[2] ?? '';
    }

    public function validateVisa(DateTime $expirationDate){
        $currentDate = new DateTime();
        if ($expirationDate >= $currentDate) {
            return true;
        }
        return false;
    }

    public function calculations($details){
        $data = [
            'Tax' => $this->cardTax, 
            'Total Price' => $details + ($details * $this->cardTax)
        ];

        return $data;
    }


    public function processPayment($details){
        if($this->validateVisa($this->expirationDate)){
            return ($details + $details * $this->cardTax);
        }

        return false;
    }
}

?>