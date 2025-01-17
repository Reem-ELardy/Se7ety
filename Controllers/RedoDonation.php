<?php
require_once 'DonationController.php';  // Include the SignupController

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the form submission
    $type = htmlspecialchars($_POST['Type']);
    $donationID = intval($_POST['donationID']);

    // Call the method in the DonationController to approve the donation
    $controller = new DonationController();
    $controller->RedoDonation($type, $donationID);

}

?>