<?php
require_once __DIR__ . '/../DB-creation/IDatabase.php';
require_once __DIR__ . '/../DB-creation/DBProxy.php';
require_once 'MedicalDonation.php';
require_once "IMedicalDonationState.php";
require_once "Medical.php";
class MedicalpendingState implements IMedicalDonationState{
    public function ProsscingDonation(MedicalDonation $donation): void{
        $MedicalItems=$donation->getMedicalItems();
        if (!is_array($MedicalItems)) {
            throw new Exception("MedicalItems must be an array. Received: " . gettype($MedicalItems));
        }

        foreach ($MedicalItems as $item) {
            if (!isset($item['medicalname'], $item['medicaltype'], $item['quantity'])) {
        throw new Exception("Invalid medical item format.");
    }
            $medical=new Medical();
            $Medicalname=$item['medicalname'];
            echo $Medicalname; echo"\n";
            $Medicaltype=$item['medicaltype'];
            $quantity=$item['quantity'];
            if($medical->FindByName($Medicalname)){
                $medicalid=$medical->getId();
                $donation->saveMedicalItems($medicalid,$quantity);

            }
            else{
                $medical=new Medical(name:$Medicalname,type:$Medicaltype);
                if($medical->createMedical()){
                $medicalid=$medical->getId();
                $donation->saveMedicalItems($medicalid,$quantity);}
            }

        }

    }

    public function NextState(MedicalDonation $donation): void{
        $donation->SetState(new MedicalDoneState());
    }

}
?>