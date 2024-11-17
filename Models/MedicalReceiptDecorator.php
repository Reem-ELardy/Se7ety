<?php

class MedicalReceiptDecorator extends ReceiptDecorator {
    private array $items; 

    public function __construct(Receipt $receipt, array $items) {
        parent::__construct($receipt);


        foreach ($items as $medical => $quantity) {
            if (!$medical instanceof Medical || !is_int($quantity)) {
                throw new InvalidArgumentException("Items must be a map of Medical objects to integers.");
            }
        }

        $this->items = $items;
    }

    public function generate_receipt(): string {
        $base_receipt = parent::generate_receipt();
        $medical_items = "Medical Items:\n";

        foreach ($this->items as $medical => $quantity) {
            /** @var Medical $medical */  // Ben3rf el key eno mn type Medical da lel IDE 
            $medical_items .= "- {$medical->getName()} (Type: {$medical->getType()->value}, Expiration Date: {$medical->getExpirationDate()->format('Y-m-d')}): $quantity\n";
        }

        return $base_receipt . "\n" . $medical_items;
    }

    public function total_donation(): float {
        $total_value = 0.0;

        foreach ($this->items as $medical => $quantity) {
            /** @var Medical $medical */
            $total_value += $quantity; 
            
        }

        return parent::total_donation() + $total_value;
    }
}
?>