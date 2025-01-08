<?php

class CheckDonation implements IDonationMethodStrategy {
    private string $checkNumber;
    private DateTime $expirationDate;
    private string $bankName;

    public function __construct(string $checkNumber, DateTime $expirationDate, string $bankName) {
        $this->checkNumber = $checkNumber;
        $this->expirationDate = $expirationDate;
        $this->bankName = $bankName;
    }

    public function processDonation(float $amount, int $quantity, string $itemDescription): void {
        echo "Processing check donation of $$amount from $this->bankName with check number $this->checkNumber.\n";
    }
}



?>