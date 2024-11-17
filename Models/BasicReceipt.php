<?php

require_once 'Receipt.php';

class BasicReceipt extends Receipt {
    private $donor_name;
    private $donation_date;
    private Donate $donate;

    public function __construct($donor_name, DateTime $donation_date,Donate $donate) {
        $this->donor_name = $donor_name;
        $this->donation_date = $donation_date;
        $this->donate = $donate;
    }

    public function getDonorName() {
        return $this->donor_name;
    }


    public function setDonorName(string $donor_name) {
        $this->donor_name = $donor_name;
    }

 
    public function getDonationDate() {
        return $this->donation_date;
    }

    public function setDonationDate(DateTime $donation_date) {
        $this->donation_date = $donation_date;
    }

     public function generate_receipt(): string {
        return "Donor: {$this->donor_name}\nDate: {$this->donation_date->format('Y-m-d')}";
    }

    public function total_donation(): float {
        return 10.0; 
    }
}

    