<?php

require_once 'ReceiptDecorator.php';

class MoneyReceiptDecorator extends ReceiptDecorator {
    private float $amount; // Donation amount
    private float $tax_value; // Tax value

    /**
     * Constructor for MoneyReceiptDecorator.
     * 
     * @param Receipt $receipt The receipt being decorated.
     * @param float $amount The donation amount.
     * @param float $tax_value The tax applied to the donation.
     */
    public function __construct(Receipt $receipt, float $amount, float $tax_value) {
        parent::__construct($receipt);
        $this->amount = $amount;
        $this->tax_value = $tax_value;
    }

    // Getter and Setter methods

    /**
     * Get the donation amount.
     * 
     * @return float
     */
    public function getAmount(): float {
        return $this->amount;
    }

    /**
     * Set the donation amount.
     * 
     * @param float $amount
     */
    public function setAmount(float $amount): void {
        $this->amount = $amount;
    }

    /**
     * Get the tax value.
     * 
     * @return float
     */
    public function getTaxValue(): float {
        return $this->tax_value;
    }

    /**
     * Set the tax value.
     * 
     * @param float $tax_value
     */
    public function setTaxValue(float $tax_value): void {
        $this->tax_value = $tax_value;
    }

    // Overridden methods

    /**
     * Generate the receipt with monetary donation details appended.
     * 
     * @return string
     */
    public function generate_receipt(): string {
        $base_receipt = parent::generate_receipt(); // Get the base receipt
        return $base_receipt 
            . "\nDonation Amount: " . number_format($this->amount, 2)
            . "\nTax Value: " . number_format($this->tax_value, 2);
    }

    /**
     * Calculate the total donation, including monetary donation.
     * 
     * @return float
     */
    public function total_donation(): float {
        return parent::total_donation() + $this->amount; // Add monetary donation to the total
    }
}

?>
