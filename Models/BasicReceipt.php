<?php

require_once 'Receipt.php';

class BasicReceipt extends Receipt {
    private string $donor_name;
    private DateTime $donation_date;
    private Donate $donate;

    /**
     * Constructor to initialize BasicReceipt.
     * 
     * @param string $donor_name Name of the donor.
     * @param DateTime $donation_date Date of the donation.
     * @param Donate $donate The associated Donate object.
    */
    public function __construct(string $donor_name, DateTime $donation_date, Donate $donate) {
        parent::__construct($donate->getDonateID()); // Assuming Donate class has a getId() method
        $this->donor_name = $donor_name;
        $this->donation_date = $donation_date;
        $this->donate = $donate;
    }

    /**
     * Get the donor's name.
     * 
     * @return string
    */
    public function getDonorName(): string {
        return $this->donor_name;
    }

    /**
     * Set the donor's name.
     * 
     * @param string $donor_name
    */
    public function setDonorName(string $donor_name): void {
        $this->donor_name = $donor_name;
    }

    /**
     * Get the donation date.
     * 
     * @return DateTime
    */
    public function getDonationDate(): DateTime {
        return $this->donation_date;
    }

    /**
     * Set the donation date.
     * 
     * @param DateTime $donation_date
    */
    public function setDonationDate(DateTime $donation_date): void {
        $this->donation_date = $donation_date;
    }

    /**
     * Generate a receipt string with donor details.
     * 
     * @return string
    */
    public function generate_receipt(): string {
        return "Donor: {$this->donor_name} <br> Date: {$this->donation_date->format('Y-m-d')}";
    }

    /**
     * Calculate the total donation amount.
     * 
     * @return float
    */
    public function total_donation(){
        return 0;
    }
}
