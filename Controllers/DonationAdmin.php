<?php
require_once __DIR__ . '/../Models/MoneyDonation.php';
require_once __DIR__ . '/../Models/Donate.php';
require_once __DIR__ . '/../Models/MedicalDonation.php';
require_once __DIR__ . '/../Models/Donation.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
class DonationAdminDashboard{
    private static $user;

    public function __construct() {
        self::$user = isset($_SESSION['user']) ? unserialize($_SESSION['user']) : null;
    }

    public function  DonationAdminDashboard(){
       
        $donationList=[];
        $donation=new MoneyDonation();
        $Donations=$donation->readAllDonation();
       
        foreach($Donations as $Donation){
            $Quantity = 0;
            if ($Donation instanceof MoneyDonation) {
                $cashAmount = $Donation->getCashAmount();
            } elseif ($Donation instanceof MedicalDonation) {
                $Quantity = count($Donation->getMedicalItems());
            }

            $donationList[]=[
                'id'=>$Donation->getDonationId(),
                'Type'=>$Donation->getDonationType(),
                'Cashamount'=>$cashAmount,
                'Quantity'=>$Quantity,
                'Status'=>$Donation->getDonationStatus(),
            ];
        }

        $data=[
            'Donations'=>$donationList,
        ];

        require_once __DIR__ . '\..\Views\Donation_Admin.php';
    } 
    public function CompleteDonation(){
        if ( isset($_GET['id'])) {
            $donationId =(int) $_GET['id'];
            $Donation= new MoneyDonation();
            $Donation->readDonation($donationId);
            if($Donation->getDonationType()==="Medical"){
                $donation= new MedicalDonation();
                $donation->readDonation($donationId);
                $donation->CompleteDonation();

            }
            $Donation->CompleteDonation();
        }

        $this->DonationAdminDashboard();

    }





    

}

?>