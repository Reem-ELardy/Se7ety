<?php
require_once 'Person-Model.php';
require_once 'Volunteer-Model (1).php';
require_once 'Donor-Model.php';
require_once 'Patient-Model.php';
require_once 'Admin.php';

class PersonFactory{
   
    public function LoginFactory($email, $password, $role) {
        $user = null;
        $result = False;

        switch ($role) {
            case 'Volunteer':
                $user = new Volunteer();
                $result = $user->login($email, $password);
                break;
            case 'Donor':
                $user = new Donor();
                $result = $user->login($email, $password);
                break;
            case 'Patient':
                $user = new Patient();
                $result = $user->login($email, $password);
                break;
            case 'Admin':
                $user = new Admin();
                $result = $user->login($email, $password);
                break;
            default:
                break;
        }
        if ($result) {
            return $user;
            
        }
        else{
            return null;
        }    
    }

    public function SignupFactory($name, $age, $email, $password, $role,$AddressID,$phone, $volunteerJob, $volunteerSkill) {
        $user = null;
        $result = null;
        switch ($role) {
            case 'Volunteer':
                $user = new Volunteer(name:$name, age:$age, email:$email, password:$password, addressId:$AddressID, job: $volunteerJob, skills: $volunteerSkill);
                $result = $user->signup($name, $age, $password, $email, $phone);
                break;
            case 'Donor':
                    $user = new Donor(name:$name, age:$age, email:$email, password:$password, addressId:$AddressID);
                    $result = $user->signup($name, $age, $password, $email, $phone);
                break;
            case 'Patient':
                $user = new Patient(name:$name, age:$age, email:$email, password:$password, addressId:$AddressID);
                $result = $user->signup($name, $age, $password, $email, $phone);
                break;
            case 'Admin':
                if(strpos($email, '@event.com') !== false){
                    $user = new Admin(name:$name, age:$age, email:$email, password:$password,addressId:$AddressID, role: Role::EventAdmin);
                    $result = $user->signup($name, $age, $password, $email,$phone);
                }else if (strpos($email, '@donation.com') !== false) {
                    $user = new Admin(name:$name, age:$age, email:$email, password:$password,addressId:$AddressID, role: Role::DonationAdmin);
                    $result = $user->signup($name, $age, $password, $email,$phone);
                }else if(strpos($email, '@payment.com') !== false){
                    $user = new Admin(name:$name, age:$age, email:$email, password:$password,addressId:$AddressID, role: Role::PaymentAdmin);
                    $result = $user->signup($name, $age, $password, $email,$phone);
                }
                break;

            default:
                break;
        }
        if (!$result ) {
            return null;
        }
        else {
            return $user;
        }
        
    }
}
?>