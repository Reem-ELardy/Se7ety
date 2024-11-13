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
   
}
?>