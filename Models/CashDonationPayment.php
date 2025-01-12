<?php

class CashDonationPayment implements IDonationPaymentStrategy {
    private float $cahshTax = 0.1;

    public function calculations($details){
        $data = [
            'Tax' => $this->cahshTax, 
            'Total Price' => $details + ($details * $this->cahshTax)
        ];

        return $data;
    }

    public function processPayment($details){
        if (is_array($details)) {
            return $details[0];
        } else{
            return ($details + $details * $this->cahshTax);
        }
    }

    public function getType() {
        return 'Cash';
    }
}

?>