<?php

require_once __DIR__ . '/../Models/MoneyDonation.php';

$totalDone = 0;
$totalPending = 0;

$donation = new MoneyDonation();

$MoneydonationList = $donation->retrieveAllMoney();

foreach($MoneydonationList as $MoneyDonation){
    if($MoneyDonation->getDonationStatus() == 'Pending'){
        $totalPending += $MoneyDonation->getCashAmount();
    }else if($MoneyDonation->getDonationStatus() == 'Done'){
        $totalDone += $MoneyDonation->getCashAmount();
    }
}

$paymentData = [
    'totalDone' => $totalDone, 
    'totalPending' => $totalPending 
];
$data = ['paymentData' => $paymentData];

require_once __DIR__ . '/../Views/Payment_Admin.php';

?>