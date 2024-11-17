<?php
require_once 'ReceiptDecorator.php';

class TotalDecorator extends ReceiptDecorator {
    
    public function __construct(Receipt $receipt) {
        parent::__construct($receipt);
    }

    public function generate_receipt(): string {
        $base_receipt = parent::generate_receipt();
        $total = $this->total_donation();
        return $base_receipt . "\nTotal Donation: $total";
    }

}
