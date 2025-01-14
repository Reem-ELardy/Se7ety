<?php
require_once 'IDonationPaymentStrategy.php';

class InKindDonationPayment implements IDonationPaymentStrategy {
    private $CashDonationPayment;
    private $medicalTax = 1;

    public function __construct() {
        $this->CashDonationPayment = new CashDonationPayment();
    }

    //$details => List of medicals
    public function processPayment($details){
        $totalQuantity = 0;

        // Loop through the array to sum up the quantities
        foreach ($details as $item) {
            $totalQuantity += $item['quantity'];
        }
        $taxEquivalent = $totalQuantity * $this->medicalTax;

        return $this->CashDonationPayment->processPayment([$taxEquivalent,'Medical']);
    }

    public function calculations($details){
        $data = [
            'Tax' => $this->medicalTax, 
            'Total Price' => $this->processPayment($details)
        ];
        return $data;
    }

    public function getType() {
        return 'InKind';
    }
}

?>
