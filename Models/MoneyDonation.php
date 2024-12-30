<?php
class MoneyDonation extends Donation {
    private float $minAmount = 10.0;

    public function __construct(IDonationMethodStrategy $donationMethod, ?float $cashAmount = null) {
        // Pass the donation method, type, and cash amount to the parent constructor
        parent::__construct($donationMethod, DonationType::Money, $cashAmount);

        // Validate the cash amount if provided
        if ($cashAmount !== null) {
            $this->validateAmount($cashAmount);
        }
    }

    /**
     * Validate the donation amount.
     * 
     * @param float $amount The amount to validate.
     * @return bool True if valid.
     * @throws Exception If the amount is below the minimum.
     */
    public function validateAmount(float $amount): bool {
        if ($amount < $this->minAmount) {
            throw new Exception("The donation amount must be at least $" . $this->minAmount);
        }
        return true;
    }

    /**
     * Process the money donation.
     * 
     * @param float $amount The amount to donate.
     * @param int $quantity Not used in money donations but retained for consistency.
     * @param string $itemDescription Not used in money donations but retained for consistency.
     */
    public function process(float $amount, int $quantity, string $itemDescription): void {
        $this->validateAmount($amount); // Validate before processing
        parent::process($amount, $quantity, $itemDescription); // Call parent logic
    }
}

    ?>