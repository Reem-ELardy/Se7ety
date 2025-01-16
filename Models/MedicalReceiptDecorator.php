<?php
require_once 'ReceiptDecorator.php';

class MedicalReceiptDecorator extends ReceiptDecorator {
    private array $items; // Map of Medical objects to their quantities
    private $tax_value;
    private $total_value;

    /**
     * Constructor for MedicalReceiptDecorator.
     * 
     * @param Receipt $receipt The receipt being decorated.
     * @param array $items An associative array of Medical objects to their quantities.
     * @param array $PaymentData which consist of the total payment and the tax
     * 
    */
    public function __construct(Receipt $receipt, array $items, $PaymentData) {
        parent::__construct($receipt);
        $this->items = $items;
        $this->tax_value = $PaymentData['Tax'];
        $this->total_value = $PaymentData['Total Price'];
    }

    /**
     * Generates the receipt with additional medical items information.
     * 
     * @return string
    */
    public function generate_receipt(){
        $base_receipt = parent::generate_receipt(); // Get the base receipt
        $medical_items = [];
        foreach ($this->items as $medical) {
            $medical_items[] = [
                "Name" => $medical['medicalname'],
                "Type" => $medical['medicaltype'],
                "Quantity" => $medical['quantity']
            ];
        }

        // Append medical items and total value to the receipt
        $base_receipt['Medical Items'] = $medical_items;
        $base_receipt['Medical Tax Value'] = number_format($this->total_value, 2);

        return $base_receipt;
    }
    /**
     * Calculates the total donation, including the value of medical items.
     * 
     * @return float
    */
    public function total_donation(){
        return parent::total_donation() + $this->total_value;
    }
}
?>
