<?php
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';
require_once 'IDonationPaymentStrategy.php';
require_once 'InKindDonationPayment.php';
require_once 'Donation.php';
require_once 'Medical.php';
require_once 'IMedicalDonationState.php';
require_once 'MedeicalpendingState.php';


class MedicalDonation extends Donation {
    protected IDonationPaymentStrategy $donationMethod;
    private  $medicalItems = [];
    protected  IMedicalDonationState $state;

    public function __construct(
        $donateID = 0,
        $donationtype = 'Medical',
        // $cashamount = 0,
        $status = 'Pending',
        $isDeleted = false,
        $medicalItems = [],
        $donationID = 0
    ) {
        parent::__construct($donateID, $donationtype, $status, $isDeleted, $donationID);
        
        $this->state = new MedicalpendingState(); 
        $this->medicalItems = $medicalItems;
    }
    public function validate(){
        foreach ($this->medicalItems as &$item) {
            if ($item['quantity'] <= 0) {
                return false ;
            }
        }
        return true;
    }

    public function SetState($state){
        $this->state=$state;
    }

    public function setPaymentMethod($paymentMethod = null){
        $this->donationMethod = new InKindDonationPayment();
    }

    public function getPaymentMethod(){
        return $this->donationMethod->getType();
    }

    public function getData(){
        return $this->getMedicalItems();
    }

  
    public function addToMedicalItems( $MedicalName,  $MedicalType,  $quantity): void {
        if ($quantity < 0) {
            throw new Exception("Invalid quantity item format.");
        }
    
        // Check if the item already exists and update the quantity if found
        foreach ($this->medicalItems as &$item) {
            if ($item['medicalname'] === $MedicalName) {
                $item['quantity'] += $quantity;
                return;
            }
        }
    
        // If the item does not exist, add it to the medicalItems array
        $this->medicalItems[] = [
            'medicalname' => $MedicalName,
            'medicaltype' => $MedicalType,
            'quantity' => $quantity,
        ];
    }

    public function getMedicalItems(): array {
        return $this->medicalItems;
    }

    public function setMedicalItems(array $medicalItems): void {
        foreach ($medicalItems as $item) {
            
    
            $MedicalName = $item['medicalname'];
            $MedicalType=$item['medicaltype'];
            $quantity = $item['quantity'];
    
    
            if (!is_int($quantity) || $quantity <= 0) {
                throw new Exception("Invalid quantity. Must be a positive integer.");
            }
    
            $this->addToMedicalItems($MedicalName,$MedicalType,$quantity);
        }
    }

    public function createMedicalDonation(): bool {
       return $this->createDonation();
    }

    public function getDonationMedicalItem($id){
        $DonatoinMedical=[];
        $query = "SELECT MedicalID, Quantity FROM DonationMedical WHERE DonationID = ?";
        $stmt= $this->dbProxy->prepare($query,[$id]);
        if(!$stmt){
            return false;
        }
        $result = $stmt->get_result();
        if (!$result || $result->num_rows === 0) {
            return false; 
        }
        while ($row = $result->fetch_assoc()) {
            $DonatoinMedical[] = [
                'MedicalID' => $row['MedicalID'],
                'Quantity' => $row['Quantity']
            ];
        }
        return $DonatoinMedical;
    }

    public  function readMedicalDonation(int $donationid): bool {
        if($this->readDonation($donationid)){
            $DonatoinMedical=$this->getDonationMedicalItem($donationid);
            if(!$DonatoinMedical ){
                return false;
            }
            $medical= new Medical();
            $this->medicalItems=[];
            foreach($DonatoinMedical as $item){
                if(!$medical->readMedical($item['MedicalID'])){
                   return false;
                }
                $this->addToMedicalItems(MedicalName:$medical->getName(),MedicalType:$medical->gettype(),quantity:$item['Quantity']);
            }
        }
        return false;
    }
    
    public function deleteMedicalDonation(): bool {
       return $this->deleteDonation();
    }

    public function saveMedicalItems(int $Medicalid, int $amount): bool {
      
        $query ="INSERT INTO DonationMedical (DonationID, MedicalID, Quantity, IsDeleted)
                VALUES (?, ?, ?, 0)"; 
        $stmt= $this->dbProxy->prepare($query,[$this->getDonationId(), $Medicalid,$amount]);
        if(!$stmt){
            return false;
        }
        return true;
    }

    public function ProcessDonation(): void{
        $this->state->ProsscingDonation($this);
    }

    public function setPayment($paymentMethod, $PaymentDetails){
        $this->donationMethod = new InKindDonationPayment();
    }

    public function CompleteDonation(){
       $this->state->NextState($this);
       $this->ProcessDonation();
    }

    public function payment(){
        // $this->donationMethod = new InKindDonationPayment();
        return $this->donationMethod->processPayment($this->medicalItems);
    }

    public function calculatePayment(){
        $totaldata = $this->donationMethod->calculations($this->medicalItems);
        return $totaldata;
    }

}

?>
