<?php
require_once __DIR__ . '/../Models/MoneyDonation.php';
require_once __DIR__ . '/../Models/Donate.php';
require_once __DIR__ . '/../Models/Donor-Model.php';
require_once __DIR__ . '/../Models/MedicalDonation.php';
require_once __DIR__ . '/../Models/DonationInvoker.php';
require_once __DIR__ . '/../Models/MedicalDonationCommand.php';
require_once __DIR__ . '/../Models/MoneyDonationCommand.php';

require_once 'UserController.php';
require_once 'HomeController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
class DonationController{
    private static $user;

    public function __construct() {
        self::$user = isset($_SESSION['user']) ? unserialize($_SESSION['user']) : null;
    }

    public function home(){
        $donatesList = self::$user->getUserDonates();

        if($donatesList == null){
            $data = [
                'donationData' => null,
            ];
            require_once __DIR__ . '/../Views/Home.php';
            return;
        }

        foreach ($donatesList as $donate) {
            $cashAmount = null;
            $Quantity = null;
            $donations = $donate->getDonationDetails();
            foreach ($donations as $donation) {
                if ($donation instanceof MoneyDonation) {
                    $cashAmount = $donation->getCashAmount();
                } elseif ($donation instanceof MedicalDonation) {
                    $Quantity = count($donation->getMedicalItems());
                }
            }   
            $type = null;
            if($cashAmount != null && $Quantity != null){
                $type = 'Both';
            }else if($cashAmount != null){
                $type = 'Money';
            }elseif($Quantity != null){
                $type = 'Medical';
            }

            $donationData[] = [
                'id' => $donate->getDonateID(),
                'cashamount' => $cashAmount,
                'Quantity' =>$Quantity,
                'type' => $type,
            ];

        }

        $data = [
            'donationData' => $donationData,
        ];
        require_once __DIR__ . '/../Views/Home.php';
    }


    public function HomeProfile(){
        $userController = new UserController();
        $userController->Display(self::$user, 'Donor');
    }

    public function DonationDetails() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $donate = null;            
            $donatesList = self::$user->getUserDonates();

            // Find the matching donation
            foreach ($donatesList as $donation) {
                if ($donation->getDonateID() === (int)$id) {
                    $donate = $donation;
                    break; // Stop the loop once a match is found
                }
            }

            if ($donate === null) {
                echo "Donation with ID $id not found.";
                return;
            }
    
            // Process donation details
            $donations = $donate->getDonationDetails();
            $cashAmount = null;
            $Medical_Items = null;

            $statusMoney = null;
            $statusMedical = null;
            foreach ($donations as $donation) {
                if ($donation instanceof MoneyDonation) {
                    $cashAmount = $donation->getCashAmount();
                    $statusMoney = $donation->getDonationStatus();
                } elseif ($donation instanceof MedicalDonation) {
                    $Medical_Items = $donation->getMedicalItems();
                    $statusMedical = $donation->getDonationStatus();
                }
            }
    
            $type = null;
            if ($cashAmount !== null && $Medical_Items !== null) {
                $type = 'Both';
            } elseif ($cashAmount !== null) {
                $type = 'Money';
            } elseif ($Medical_Items !== null) {
                $type = 'Medical';
            }
    
            // Prepare data for the view
            $donationData = [
                'id' => $donate->getDonateID(),
                'type' => $type,
                'cashamount' => $cashAmount,
                'Items' => $Medical_Items,
                'StatusMoney' => $statusMoney,
                'StatusMedical' => $statusMedical,
            ];
    
            $data = [
                'donationDetails' => $donationData,
            ];
    
            // Load the view
            require_once __DIR__ . '/../Views/Donation_Details.php';
        } else {
            echo "No ID found in the URL.";
        }
    }

    public function Make_Donation()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = null;
            $donationData = [
                'money' => null,
                'medical' => []
            ];

            // Validate and process money donation
            if (isset($_POST['money_amount']) && is_numeric($_POST['money_amount'])) {
                $donationData['money'] = (float)$_POST['money_amount'];
            }

            // Validate and process medical donations
            if (isset($_POST['medical_name']) && isset($_POST['medical_type']) && isset($_POST['medical_quantity']) && $_POST['medical_name'][0] !=null) {
                $medicalNames = $_POST['medical_name'];
                $medicalTypes = $_POST['medical_type'];
                $medicalQuantities = $_POST['medical_quantity'];

                foreach ($medicalNames as $index => $name) {
                    $type = $medicalTypes[$index] ?? null;
                    $quantity = $medicalQuantities[$index] ?? null;
    
                    // Only add valid donations (non-empty and valid quantity)
                    if (!empty($name) && !empty($type) && is_numeric($quantity) && $quantity > 0) {
                        $donationData['medical'][] = [
                            'medicalname' => htmlspecialchars($name),
                            'medicaltype' => htmlspecialchars($type),
                            'quantity' => (int)$quantity
                        ];
                    }
                }
            }
            
            $data = $this->ProcessData($donationData);
            if(is_string($data)){
                $error = $data;
                require_once __DIR__. '/../Views/Make_Donation.php';
                return;
            }
            
            // Redirect to payment page
            require_once __DIR__. '/../Views/Payment_Page.php';
        }
    }

    public function ProcessData($donationData){
        $donorID = self::$user->getID();
        $donate = new Donate($donorID);
        $donate->createDonate();

        $sessionData = [];
        if (!empty($donationData['money'])) {
            $moneyDonation = new MoneyDonation($donate->getDonateID(), $donationData['money']);
            $donationID = uniqid('donation_', true);
            $moneyDonation->processDonationTemplate($donationID);
            $sessionData[] = ['id'=> $donationID, 'donateID' => $donate->getDonateID(), 'DonationID' => $moneyDonation->getDonationId(), 'Data' => $moneyDonation->getCashAmount(), 'Type' => 'Money'];
        }

        if (!empty($donationData['medical'])) {
            $medicalDonation = new MedicalDonation($donate->getDonateID(), medicalItems: $donationData['medical']);
            $donationID = uniqid('donation_', true);
            $medicalDonation->processDonationTemplate($donationID);
            $sessionData[] = ['id'=> $donationID, 'donateID' => $donate->getDonateID(), 'DonationID' => $medicalDonation->getDonationId(), 'Data' => $medicalDonation->getMedicalItems(), 'Type' => 'Medical'];
        }

        foreach ($sessionData as $data) {
            $donationID = $data['id'];
            if (isset($_SESSION['donations'][$donationID]['donation_step']) && 
                $_SESSION['donations'][$donationID]['donation_step'] === 'Validation_Failed') {
                $error = "Donation Failed due to false data";
                return $error;
            }
        }

        $_SESSION['sessionData'] = $sessionData;
        session_write_close();
        return true;
    }

    public function Calculation() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the posted data
            $data = json_decode(file_get_contents('php://input'), true);
            $paymentMethod = $data['paymentMethod'];
    
            // Session data processing
            $sessionData = $_SESSION['sessionData'];
    
            $taxAmount = 0;
            $totalAmount = 0;
            
            foreach ($sessionData as $Data) {
                $Donation =$this->DonationFactoryForPayment($Data, $paymentMethod);
                $Donation->processDonationTemplate($Data['id']);
                $totalData = $_SESSION['donations'][$Data['id']]['Totaldata'];
                $taxAmount += $totalData['Tax'];
                $totalAmount += $totalData['Total Price'];
            }

            echo json_encode([
                'tax' => $taxAmount,
                'total' => $totalAmount
            ]);
        }
    }


    public function DonationFactoryForPayment($data, $paymentMethod){
        $Donation = null;
        $Type = $data['Type'];
        if ($Type == 'Money') {
            $Donation = new MoneyDonation($data['donateID'], $data['Data'], $data['DonationID']);
            $Donation->setPaymentMethod($paymentMethod);
        }else if ($Type == 'Medical') {
            $Donation = new MedicalDonation($data['donateID'], medicalItems: $data['Data'], donationID: $data['DonationID']);
            $Donation->setPaymentMethod($paymentMethod);
        }
        return $Donation;
    }

    public function payment(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if (isset($_POST['payment-method'])) {
                $paymentMethod = $_POST['payment-method'];
            } else {
                echo "No payment method was selected.";
            }

            $PaymentData = [];

            if($paymentMethod == 'card'){
                $Card_Number = $_POST['card-number'];
                $expirationDate = new DateTime($_POST['expiry-date']);
                $bankName = $_POST['cvv'];
                $PaymentData = [$Card_Number, $expirationDate, $bankName];

            }else if($paymentMethod == 'ewallet'){
                $ewalletNumber = $_POST['ewallet-number'];
                $PaymentData = [$ewalletNumber];
            }

            $sessionData = $_SESSION['sessionData'];

            $donate = new Donate(self::$user->getID(), $sessionData[0]['donateID']);
            foreach ($sessionData as $Data) {
                $Donation =$this->DonationFactoryForPayment($Data, $paymentMethod);
                $donate->addDonation($Donation);
                $_SESSION['donations'][$Data['id']] = ['donation_step' => 'payment_done'];
                $Donation->setPaymentMethod($paymentMethod, $PaymentData);
                $Donation->processDonationTemplate($Data['id']);
            }
            unset($_SESSION['sessionData']);

            $this->Reciept($donate, $paymentMethod);
        }
    }

    public function Reciept($donate, $paymentMethod){
        $receipt = $donate->generateReceipt(self::$user->getName());
        $transaction_id = $this->generate_payment_transaction_id();
        $receipt[] = ['Payment_Method' => $paymentMethod, 'Transaction_ID' => $transaction_id];

        $data = [
            'receiptDetails' => $receipt,
        ];

        require_once __DIR__. '/../Views/Receipt.php';
    }

    public function generate_payment_transaction_id(): string {
        $timestamp = date("mdHi"); 
        $random = strtoupper(bin2hex(random_bytes(2))); 
    
        return "{$timestamp}-{$random}";
    }

    public function UndoDonation($type,$donatenID){
        $donate = null;
        $donatesList = self::$user->getUserDonates();
        echo $donatenID;

        // Find the matching donation
        foreach ($donatesList as $donation) {
            if ($donation->getDonateID() === (int)$donatenID) {
                $donate = $donation;
                break;
            }
        }
        $donations = $donate->getDonationDetails();

        if(count($donations) == 1){
            $donate->deleteDonate();
        }
        
        foreach ($donations as $donation) {
            if ($donation->getDonationType() == $type) {
                if($type == 'Money'){
                    $Moneycommand= new MeoneyDonationCommand($donation);
                    $invoker= new DonationInvoker();
                    $invoker->setCommand($Moneycommand);
                    $invoker->executeUndoCommand();
                }else if ($type = 'Medical'){
                    $Medicalcommand= new MedicalDonationCommand($donation);
                    $invoker= new DonationInvoker();
                    $invoker->setCommand($Medicalcommand);
                    $invoker->executeUndoCommand();
                }
            }
        }

        $homeController = new HomeController();
        $homeController->homeDoner();
        exit();
    }

    public function RedoDonation($type,$donatenID){
        $sessionData = [];

        $donatesList = self::$user->getUserDonates();
        foreach ($donatesList as $donate) {
            if ($donate->getDonateID() === (int)$donatenID) {
                $donations = $donate->getDonationDetails();
                foreach ($donations as $donation) {
                    if ($donation->getDonationType() == $type) {
                        $this->RedoHelper($donation, $type);
                    }
                }
                break;
            }
        }
        $_SESSION['sessionData'] = $sessionData;
        session_write_close();

        require_once __DIR__. '/../Views/Payment_Page.php';
    }

    public function RedoHelper($donation, $type){
        $donorID = self::$user->getID();
        $donate = new Donate($donorID);
        $donate->createDonate();

        if($type == 'Money'){
            $Donation = new MoneyDonation($donate->getDonateID(), $donation->getData());
            $Moneycommand= new MeoneyDonationCommand($Donation);
            $invoker= new DonationInvoker();
            $invoker->setCommand($Moneycommand);
            list($donation_id, $moneyDonation) = $invoker->executeRedoCommand();
            $sessionData[] = ['id'=> $donation_id, 'donateID' => $donate->getDonateID(), 'DonationID' => $Donation->getDonationId(), 'Data' => $Donation->getCashAmount(), 'Type' => 'Money'];

        }else if ($type = 'Medical'){
            $Donation = new MedicalDonation($donate->getDonateID(), $donation->getData());
            $Medicalcommand= new MedicalDonationCommand($Donation);
            $invoker= new DonationInvoker();
            $invoker->setCommand($Medicalcommand);
            list($donation_id, $medicalDonation) = $invoker->executeRedoCommand();
            $sessionData[] = ['id'=> $donation_id, 'donateID' => $donate->getDonateID(), 'DonationID' => $Donation->getDonationId(), 'Data' => $Donation->getMedicalItems(), 'Type' => 'Medical'];
        }
        $_SESSION['sessionData'] = $sessionData;
        session_write_close();
    }

    
    
    
}

?>