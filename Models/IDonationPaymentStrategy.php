<?php

interface IDonationMethodStrategy {
    public function processDonation(float $amount, int $quantity, string $itemDescription): void;
}

?>
