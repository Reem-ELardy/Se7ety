<?php

require_once 'Receipt.php';

abstract class ReceiptDecorator extends Receipt {
    protected Receipt $receipt; // Composition: Wrapping a Receipt instance

      /**
     * Constructor for TotalDecorator.
     * 
     * @param Receipt $receipt The receipt being decorated.
     */
    public function __construct(Receipt $receipt) {
        // Initialize the parent class
        parent::__construct($receipt->getDonateId(), $receipt->getId());
        $this->receipt = $receipt;
    }

    /**
     * Generates the receipt by delegating to the wrapped receipt.
     * 
     * @return string
     */
    public function generate_receipt(){
        return $this->receipt->generate_receipt();
    }

    /**
     * Calculates the total donation by delegating to the wrapped receipt.
     * 
     * @return 
     */
    public function total_donation(){
        return $this->receipt->total_donation();
    }
}

?>
