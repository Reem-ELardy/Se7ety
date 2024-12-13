<?php
class MoneyDonation extends Donation {
    private float $minAmount = 10.0;

    public function __construct(IDonationMethodStrategy $donationMethod) {
        // Pass the donationMethod and DonationType::Money to the parent constructor
        parent::__construct($donationMethod, DonationType::Money);
    }

    public function validateAmount(float $amount): bool {
        if ($amount < $this->minAmount) {
            throw new Exception("The donation amount must be at least $" . $this->minAmount);
        }
        return true;
    }

    public function process(float $amount, int $quantity, string $itemDescription): void {
        $this->validateAmount($amount); // Validate before processing
        parent::process($amount, $quantity, $itemDescription); // Call parent logic
    }
}
?>