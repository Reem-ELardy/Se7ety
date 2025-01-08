<?php
class EWalletDonation implements IDonationMethodStrategy {
    private int $transactionID;

    public function __construct(int $transactionID) {
        $this->transactionID = $transactionID;
    }

    public function processDonation(float $amount, int $quantity, string $itemDescription): void {
        echo "Processing e-wallet donation of $$amount with transaction ID $this->transactionID.\n";
    }
}

?>