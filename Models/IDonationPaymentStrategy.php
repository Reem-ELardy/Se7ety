<?php

interface IDonationPaymentStrategy {
    public function calculations($details);
    public function processPayment($details);
}

?>