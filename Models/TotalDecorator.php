<?php

require_once 'ReceiptDecorator.php';

class TotalDecorator extends ReceiptDecorator {

    /**
     * Constructor for TotalDecorator.
     * 
     * @param Receipt $receipt The receipt being decorated.
     */
    public function __construct(Receipt $receipt) {
        parent::__construct($receipt);
    }

    /**
     * Generate the receipt with the total donation appended.
     * 
     * @return string The complete receipt string with the total donation value.
     */
    public function generate_receipt(){
        $base_receipt = parent::generate_receipt(); // Get the base receipt details
        $total = $this->total_donation(); // Calculate the total donation
        $base_receipt['Total Donation'] = number_format($total, 2);
        return $base_receipt; // Format the total donation
    }

    /**
     * Calculate the total donation (delegates to the parent).
     * 
     * @return float The total donation value.
     */
    public function total_donation(){
        return parent::total_donation(); // Simply pass through to the parent method
    }
}

?>
