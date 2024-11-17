<?php

require_once 'ReceiptDecorator.php';

class MoneyReceiptDecorator extends ReceiptDecorator {
    private $amount;
    private $tax_value;

    public function __construct(Receipt $receipt, float $amount, float $tax_value) {
        parent::__construct($receipt);
        $this->amount = $amount;
        $this->tax_value = $tax_value;
    }

    public function getAmount(): float {
        return $this->amount;
    }

   
    public function setAmount(float $amount): void {
        $this->amount = $amount;
    }

   
    public function getTaxValue(): float {
        return $this->tax_value;
    }

   
    public function setTaxValue(float $tax_value): void {
        $this->tax_value = $tax_value;
    }

    public function generate_receipt(): string {
        $base_receipt = parent::generate_receipt();
        return $base_receipt . "\nDonation Amount: $this->amount\nTax Value: $this->tax_value";
    }

    public function total_donation(): float {
        return parent::total_donation() + $this->amount;
    }

  
}
?>