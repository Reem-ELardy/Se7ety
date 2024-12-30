<?php

class MedicalReceiptDecorator extends ReceiptDecorator {
    private array $items; // Map of Medical objects to their quantities

    /**
     * Constructor for MedicalReceiptDecorator.
     * 
     * @param Receipt $receipt The receipt being decorated.
     * @param array $items An associative array of Medical objects to their quantities.
     * 
     * @throws InvalidArgumentException If items are not valid.
     */
    public function __construct(Receipt $receipt, array $items) {
        parent::__construct($receipt);

        foreach ($items as $medical => $quantity) {
            if (!$medical instanceof Medical || !is_int($quantity)) {
                throw new InvalidArgumentException("Items must be a map of Medical objects to integers.");
            }
        }

        $this->items = $items;
    }

    /**
     * Generates the receipt with additional medical items information.
     * 
     * @return string
     */
    public function generate_receipt(): string {
        $base_receipt = parent::generate_receipt(); // Get the base receipt
        $medical_items = "Medical Items:\n";

        foreach ($this->items as $medical => $quantity) {
            /** @var Medical $medical */
            $medical_items .= "- {$medical->getName()} (Type: {$medical->getType()->value}, Expiration Date: {$medical->getExpirationDate()->format('Y-m-d')}): $quantity\n";
        }

        return $base_receipt . "\n" . $medical_items;
    }

    /**
     * Calculates the total donation, including the value of medical items.
     * 
     * @return float
     */
    public function total_donation(): float {
        $total_value = 0.0;

        foreach ($this->items as $medical => $quantity) {
            /** @var Medical $medical */
            $total_value += $medical->getValue() * $quantity; // Assuming Medical has a getValue() method
        }

        return parent::total_donation() + $total_value;
    }
}
?>
