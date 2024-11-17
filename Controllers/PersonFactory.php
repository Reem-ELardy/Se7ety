<?php
require_once __DIR__ . '/../Models/Person-Model.php';
require_once __DIR__ . '/../Models/Volunteer-Model.php';
require_once __DIR__ . '/../Models/Donor-Model.php';
require_once __DIR__ . '/../Models/Patient-Model.php';

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
    public function SignupFactory($name, $age, $email, $password, $role,$AddressID) {
        $user = null;
        $result = null;
        switch ($role) {
            case 'Volunteer':
                $user = new Volunteer(name:$name, age:$age, email:$email, password:$password,addressId:$AddressID);
                $result = $user->signup($name, $age, $password, $email);
                break;
            case 'Donor':

                $user = new Donor(name:$name, age:$age, email:$email, password:$password,addressId:$AddressID);
                $result = $user->signup($name, $age, $password, $email);
                break;
            case 'Patient':
                $user = new Patient(name:$name, age:$age, email:$email, password:$password,addressId:$AddressID);
                $result = $user->signup($name, $age, $password, $email);
                
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