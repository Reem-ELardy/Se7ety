<?php

class CashDonation implements IDonationMethodStrategy {
    public function processDonation(float $amount, int $quantity, string $itemDescription): void {
        echo "Processing cash donation of $$amount.\n";
    }
}

?>