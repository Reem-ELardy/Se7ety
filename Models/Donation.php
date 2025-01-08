<?php
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';

enum DonationType: string {
    case Medical = 'Medical';
    case Money = 'Money';
}

enum Status: string {
    case Pending = 'Pending';
    case Done = 'Done';
}

abstract class Donation {
    private ?int $id = null;
    protected int $DonateID;
    protected DonationType $donationtype;
    private Status $status;
    private ?float $cashamount; 
    private bool $IsDeleted;
    protected $dbProxy;


    public function __construct(int $donateID=null, String $donationtype= '', ?float $cashamount = 0, String $status = 'Pending', bool $isDeleted = false) {
        $this->dbProxy = new DBProxy('user');
        $this->donationtype = DonationType::from($donationtype);
        $this->status = Status::from($status);
        $this->cashamount = $cashamount; 
        $this->IsDeleted = $isDeleted;
        $this->DonateID = $donateID;
    }

    //Setters and Getters
    public function getDonationId(): int {
        return $this->id;
    }

    public function setDonationId(int $id): void {
        $this->id = $id;
    }

    public function getDonateId(): int {
        return $this->DonateID;
    }

    public function setDonateId(int $DonateID): void {
        $this->DonateID = $DonateID;
    }

    public function getCashAmount(): ?float {
        return $this->cashamount;
    }

    public function setDonationStatus(String $status): void {
        $this->status = Status::from($status);
    }

    public function getDonationStatus(): String {
        return $this->status->value;
    }

    public function setCashAmount($cashamount): void {
        if ($this->donationtype === DonationType::Money) {
            $this->cashamount = $cashamount;
        } else {
            throw new Exception("Cash amount can only be set for Medical donations.");
        }
    }

    public function getDonationType(): String {
        return $this->donationtype->value;
    }

    public function setDonationType(String $donationType): void {
        $this->donationtype = DonationType::from($donationType);
    }

    //Template Functions
    public function processDonationTemplate($details, $donation_id) {
        // Start session if it's not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    
        // Retrieve the donation data for this specific donation ID
        if (!isset($_SESSION['donations'][$donation_id])) {
            return;
        }
        $donation = &$_SESSION['donations'][$donation_id];
    
        // Process donation steps based on the session state
        if ($donation['donation_step'] == 'Started') {
            if (!$this->validate($details)) {
                $donation['donation_step'] = 'Validation Failed';
                return;
            }
            // $this->validate($details);
            $this->createDonation();
            $this->ProcessDonation();
            $donation['donation_step'] = 'PaymentMethod';
            return;
        }
    
        if ($donation['donation_step'] === 'PaymentMethod') {
            $paymentMethod = $this->getState('Method', $donation_id);
            $this->CalculatePayment($paymentMethod, $details, $donation_id);
            $donation['donation_step'] = 'payment_done';
            return;
        }
    
        if ($donation['donation_step'] === 'payment_done') {
            $paymentMethod = $donation['Method'];
            echo $paymentMethod;
            if (strtolower($paymentMethod) != 'cash' && strtolower($paymentMethod) != 'inkind') {
                $PaymentDetails = $this->getState('PaymentDetails', $donation_id);
                if (!$this->Payment($paymentMethod, $PaymentDetails, $details)) {
                    $donation['donation_step'] = 'Payment Failed';
                    return;
                }
                $this->CompleteDonation();
            }
        }

        unset($_SESSION['donations'][$donation_id]);
        return;
    }
    
    
    abstract protected function validate($data);
    abstract protected function CalculatePayment($paymentMethod, $details, $donationSessionID);
    protected function saveState($key, $value, $donationSessionID) {
        $donation = &$_SESSION['donations'][$donationSessionID];
        $donation[$key] = $value;
    }
    protected function getState($key, $donation_id) {
        $donation = &$_SESSION['donations'][$donation_id];
        return $donation[$key];
    }

    //Payment Funcion used for strategy
    abstract protected function Payment($paymentMethod, $PaymentDetails, $details);

    
    //State Funtions
    abstract public function ProcessDonation();
    abstract public function CompleteDonation();


    //CRUD Functions
    public function createDonation() {      
        $query = "INSERT INTO DONATION (DonateID, Type, CashAmount, Status, IsDeleted) VALUES (?, ?, 0, ?, 0)";
        $stmt = $this->dbProxy->prepare($query, [$this->DonateID, $this->donationtype->value, $this->status->value]);

        if ($stmt) {
            $this->id = $this->dbProxy->getInsertId();
            return true;
        }
        return $stmt;
    }

    public function readDonation(int $donationId): bool {
        $query = "SELECT DonateID, Type, CashAmount, Status FROM Donation WHERE ID = ? AND IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($query, [$donationId]);

        if ($stmt) {
            $stmt->store_result();
            $stmt->bind_result($DonateID, $Type, $CashAmount, $Status);
    
            if ($stmt->fetch()) {
                $this->DonateID = $DonateID;
                $this->donationtype = DonationType::from($Type);
                $this->status = Status::from($Status);
                if ($this->donationtype === DonationType::Money) {
                    $this->cashamount = $CashAmount;
                }
                return true;
            }
        }
    
        return false;
    }

    public function updateDonation(): bool {
        $query = "UPDATE DONATION SET Status = ? WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->cashamount, $this->status->value, $this->id]);

        if (!$stmt) {
            return false;
        }

        return true;
    }

    public function deleteDonation(): bool {
        $query = "UPDATE DONATION SET IsDeleted = 1 WHERE ID = ?";
        $stmt = $this->dbProxy->prepare($query, [$this->id]);

        if (!$stmt) {
            return false;
        }

        return true;
    }

    public function readDonateDonation(int $donateID): array {
        $query = "SELECT * FROM Donation WHERE DonateID = ? AND IsDeleted = 0";
        $stmt = $this->dbProxy->prepare($query, [$donateID]);

        if ($stmt) {
            $stmt->store_result();
            $stmt->bind_result($DonationID, $DonateID, $Type, $cashamount, $Status, $IsDeleted);
    
            $donations = [];
            while ($stmt->fetch()) {
                $donation = new Donation($DonateID, $Type, $cashamount, $Status);
                if($type = DonationType::Medical){
                    $donation = new MedicalDonation($DonateID, $Status);
                    $donation->getMedicalItems();
                }else if($type = DonationType::Money){
                    $donation = new MoneyDonation($DonateID, $Type, $cashamount, $Status);
                }
                $donation->setDonationId($DonationID);
                $donations[] = [
                    $donation
                ];
            }
            return $donations;
        }

        return [];
    }

}

?>