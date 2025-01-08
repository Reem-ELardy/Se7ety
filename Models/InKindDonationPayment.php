<?php
require_once 'IDonationMethodStrategy.php';

class InKindDonation implements IDonationMethodStrategy {
        public function processDonation(float $amount, int $quantity, string $itemDescription): void {
            echo "Processing in-kind donation: $quantity $itemDescription(s).\n";
        }
    }

?>
