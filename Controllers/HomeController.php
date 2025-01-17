<?php
require_once 'DonationController.php'; 
require_once 'EventAdminController.php';
require_once 'PatientController.php';
require_once 'EventController.php';
require_once 'DonationAdmin.php';


class HomeController {
    public function homeDoner() {
        $controller = new DonationController();
        $controller->home();
    }

    public function homeEventAdmin(){
        ob_start();

        $controller = new EventAdminController();
        $controller->GetEvents();

        header("Location: http://localhost:3000/Controllers/EventAdminHomeController.php");
        exit;
        ob_end_flush();
    }

    public function homePatient(){
        ob_start();
        $controller = new PatientController();
        $controller->home();

        // Set the desired 'Location' header
        header("Location: http://localhost:3000/Controllers/PatinetHomeController.php");
        exit;
        ob_end_flush();
    }

    public function homePaymentAdmin(){
        ob_start();
        echo'here';

        // Set the desired 'Location' header
        header("Location: http://localhost:3000/Controllers/PaymentAdminController.php");
        exit;
        ob_end_flush();
    }
    public function VolunterDashboard(){
        ob_start();
        $controller = new EventController();
        $controller->volunteerDashBoard();

        // Set the desired 'Location' header
        header("Location: http://localhost:3000/Controllers/PatinetHomeController.php");
        exit;
        ob_end_flush();
    }
    public function homeDonationAdmin(){
        ob_start();
        echo'here';
        $controller = new DonationAdminDashboard();
        $controller->DonationAdminDashboard();

        // Set the desired 'Location' header
        header("Location: http://localhost:3000/Controllers/Admin.php");
        exit;
        ob_end_flush();
    }
}
?>