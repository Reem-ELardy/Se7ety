<?php

require_once 'Receipt.php';

abstract class ReceiptDecorator extends Receipt {
    protected $receipt;

    public function __construct(Receipt $receipt) {
        $this->receipt = $receipt;
    }

    public function generate_receipt(): string {
        return $this->receipt->generate_receipt();
    }

    public function total_donation(): float {
        return $this->receipt->total_donation();
    }

}

?>

