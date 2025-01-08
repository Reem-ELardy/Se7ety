<?php
require_once 'MedicalDonation.php';
require_once "IMedicalDonationState.php";
require_once "Medical.php";
class MedicalDoneState implements IMedicalDonationState{
    public function ProsscingDonation(MedicalDonation $donation): void{
        if( $donation->updateDonation()){
            $DonationMedicalItems=$donation->getDonationMedicalItem($donation->getDonationId());
            if (!is_array($DonationMedicalItems)) {
                throw new Exception("MedicalItems must be an array. Received: " . gettype($DonationMedicalItems));
            }

            foreach ($DonationMedicalItems as $item) {
                if (!isset($item['MedicalID'], $item['Quantity'])) {
                throw new Exception("Invalid medical item format.");}
                $MedicalID=$item['MedicalID'];
                $Quantity =$item['Quantity'];
            
                $medical=new Medical();
                if($medical->incrementMedicalquantity($MedicalID,$Quantity))
                {
                    continue;
                }
                else{
                    throw new Exception("Medical quantaty is not updated  " );
                }


            }
            
        }
        else{
            throw new Exception("Medical status  is not updated  " );


        }
        

    }

    public function NextState(MedicalDonation $donation): void{}

}
?>