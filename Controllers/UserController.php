<?php
require_once __DIR__ . '/../Models/PersonFactory.php';
require_once __DIR__ . '/../Models/Address-Model.php';
require_once __DIR__ . '/../Models/Skill.php';
require_once __DIR__ .'/../Models/CommunicationFacade.php';
require_once __DIR__ .'/../Models/SMSComm.php';
require_once __DIR__ .'/../Models/EmailComm.php';
require_once __DIR__ .'/../Models/SocialMediaComm.php';
require_once __DIR__ .'/../Models/SocialMediaToJson.php';
require_once __DIR__ .'/../Models/Subject.php';


require_once 'HomeController.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class UserController {

    public function SignupValidation() {
        $model = new Address(); 
        $skillmodel = new Skills();
        $addressList = [];
        $skillList = $skillmodel->getAllSkills();

        if (!$model->GetWholeAddressesList($addressList)) {
            die("Failed to load address data.");
        }

        $PersonFactory=new PersonFactory();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = trim($_POST["name"]);
            $age = trim($_POST["age"]);
            $email = $_POST["email"];
            $password = $_POST["password"];
            $AddressID=$_POST["DistrictAdress"];
            $role = $_POST["role"];
            $error = "";
            $phoneNumber=$_POST["phone"];
            $volunteerJob = null;
            $volunteerSkill = [];

            if($role == 'Volunteer'){
                if (isset($_POST['volunteerJob']) && !empty($_POST['volunteerJob'])) {
                    $volunteerJob = $_POST['volunteerJob'];
                    echo "Volunteer Job: " . htmlspecialchars($volunteerJob) . "<br>";
                } else {
                    echo "Volunteer Job is not selected.<br>";
                }
        
                if (isset($_POST['volunteerSkill']) && !empty($_POST['volunteerSkill'])) {
                    $volunteerSkill = $_POST['volunteerSkill'];
                    echo "Volunteer Skill: " . htmlspecialchars($volunteerSkill) . "<br>";
                } else {
                    echo "Volunteer Skill is not selected.<br>";
                }
            }

            if (!is_numeric($age) || $age < 18) {
                $error = "Invalid age, please enter it again, for example 26.";
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email, please enter it again, e.g., email23@example.com";
            }
            
            $IsValidPassword = strlen($password) >= 8 &&
                               preg_match('/[A-Za-z]/', $password) &&
                               preg_match('/\d/', $password) &&
                               preg_match('/[@$!%*?&]/', $password);

            if (!$IsValidPassword) {
                $error = "Invalid password, please enter it again. The password should contain at least 8 characters, one letter, one number, and one special character.";
            }

            if ($phoneNumber && strlen($phoneNumber) == 10) {
                $error = "Invalid phone number, please enter it again. The phone number should be 11 numbers";
            }

            if (empty($error)) {
                $result = $PersonFactory->SignupFactory($name, $age, $email, $password, $role,$AddressID, $phoneNumber, $volunteerJob, $volunteerSkill);
                $_SESSION['user'] = serialize($result);

                if ($result == null) {
                    $error ="This user already exists.";
                } else {
                    if($role != 'Admin'){
                        $smsComm = new SMS();
                        $emailComm = new Email();
                        $socialMediaComm = new SocialMedia(PlatformType::Instagram, $result->getEmail());
                        // Step 5: Instantiate CommunicationFacade
                        $communicationFacade = new CommunicationFacade(
                            $emailComm,
                            $smsComm,
                            $socialMediaComm,
                            $result,
                        );
                        
                        $communicationFacade->sendSignupThankYou();
                    }

                    $this->goto($result, $email);
                    exit();
                }
            }
        }
        $data = [
            'addressList' => $addressList,
            'skillList' => $skillList
        ];
       require_once __DIR__ . '/../Views/Signup.php';
   }

   public function goto($result, $email){
        $homeController = new HomeController();
        if($result instanceof Donor){
            $homeController->homeDoner();
        }else if($result instanceof Patient){
            $homeController->homePatient();
        }else if($result instanceof Volunteer){
            $homeController->VolunterDashboard();
        }else if(strpos($email, '@event.com') !== false){
            $homeController->homeEventAdmin();
        }else if (strpos($email, '@donation.com') !== false) {
            $homeController->homeDonationAdmin();
        }else if(strpos($email, '@payment.com') !== false){
            $homeController->homePaymentAdmin();
        }
   }
    
    public function LoginValidation() {
        $PersonFactory= new PersonFactory();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST["email"];
            $password = $_POST["password"];
            $role = $_POST["role"];
            $error = "";

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email, please enter it again.";
                require __DIR__ . '/../Views/Login.php';
                return;
            }

            if (empty($error)) {
                $result = $PersonFactory->LoginFactory($email, $password, $role);

                //Added
                $_SESSION['user'] = serialize($result);
             
                if ($result != NULL) {
                    $this->goto($result, $email);
                    exit();
                } else {
                    $error = "Invalid credentials, please try again.";
                    require __DIR__ . '/../Views/Login.php';
                }
            }
        }
      
    }

    public function Display($result, $role){
        $wholeAddress="";
        $Address=new Address();
        $Address->GetWholeAddress($result->getAddressId(),$wholeAddress);
        $deleted = false;

        $_SESSION['name'] = $result->getName();
        $_SESSION['age'] = $result->getAge();
        $_SESSION['address'] = $result->getAddressId();
        $_SESSION['wholeAddress'] = $wholeAddress;
        $_SESSION['email'] = $result->getEmail();
        $_SESSION['role'] = $role;
        $_SESSION['AddressId'] = $result->getAddressId();
        require __DIR__ . "/../Views/display.php";
    }

    public function updateUser() {
        $user = NULL;

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $Id = $_POST["Id"];
            $name = trim($_POST["name"]);
            $age = trim($_POST["age"]);
            $email = $_POST["email"];
            $password = $_POST["password"];
            $role = $_POST["role"];
            $AddressID=$_POST["DistrictAdress"];

            if (!is_numeric($age) || $age < 18) {
                $error = "Invalid age, please enter it again, for example 18.";
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email, please enter it again, e.g., email23@example.com";
            }

            $IsValidPassword = strlen($password) >= 8 &&
                               preg_match('/[A-Za-z]/', $password) &&
                               preg_match('/\d/', $password) &&
                               preg_match('/[@$!%*?&]/', $password);

            if (!$IsValidPassword) {
                $error = "Invalid password, please enter it again. The password should contain at least 8 characters, one letter, one number, and one special character.";
            }

            if (!empty($error)) {
                $model = new Address(); 

                $addressList = [];

                if (!$model->GetWholeAddressesList($addressList)) {
                    die("Failed to load address data.");
                }
                $data = [
                    'Id' => $Id,
                    'name' => trim($_POST["name"]),
                    'age' => trim($_POST["age"]),
                    'DistrictID' => $AddressID,
                    'CityID' => $_POST['CityAdress'],
                    'email' => $email,
                    'role'=> $role,
                    'addressList' => $addressList,
                ];
                require __DIR__ . '/../Views/Update.php';
                return;
            }

            switch ($role) {
                case 'Volunteer':
                    $user = new Volunteer();
                    $result = $user->readVolunteer($Id);
                    $user->setName($name);
                    $user->setAge($age);
                    $user->setEmail($email);
                    $user->setPassword($password);
                    $user->setAddressId($AddressID);
                    $result = $user->updateVolunteer();
                    break;
                case 'Donor':
                    $user = new Donor();
                    $result = $user->readDonor($Id);
                    $user->setName($name);
                    $user->setAge($age);
                    $user->setEmail($email);
                    $user->setPassword($password);
                    $user->setAddressId($AddressID);
                    $result = $user->updateDonor();
                    break;
                case 'Patient':
                    $user = new Patient();
                    $result = $user->readPatient($Id);
                    $user->setName($name);
                    $user->setAge($age);
                    $user->setEmail($email);
                    $user->setPassword($password);
                    $user->setAddressId($AddressID);
                    $result = $user->updatePatient();
                    break;
            }
            if ($result) {
                $_SESSION['user'] = serialize($user);
                $this->Display($user, $role);
            } else {
                require __DIR__ ."/../Views/Update.php";
            }
        }
    }

    public function Delete($email, $role) {
        $result = null;
        $deleted = NULL;

        switch ($role) {
            case 'Volunteer':
                $user = new Volunteer();
                $result = $user->findByEmail($email);
                if ($result) {
                    $user->delete($user->getId());
                }
                break;
            case 'Donor':
                $user = new Donor();
                $result = $user->findByEmail($email);
                if ($result) {
                    $user->delete($user->getId());
                }
                break;
            case 'Patient':
                $user = new Patient();
                $result = $user->findByEmail($email);
                if ($result) {
                    $user->delete($user->getId());
                }
                break;
            default:
                break;
        }

        session_unset();
        session_destroy();

        $deleted = true;
        require __DIR__ . "/../Views/display.php";
    }

    
    public function Update() {

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST["email"];
            $role = $_POST["role"];
            $addressID = $_POST["AddressId"];
            $Id = NULL;

            switch ($role) {
                case 'Volunteer':
                    $user = new Volunteer();
                    $result = $user->findByEmail($email);
                    if ($result) {
                        $Id = $user->getId();
                    }
                    break;
                case 'Donor':
                    $user = new Donor();
                    $result = $user->findByEmail($email);
                    if ($result) {
                        $Id = $user->getId();
                    }
                    break;
                case 'Patient':
                    $user = new Patient();
                    $result = $user->findByEmail($email);
                    if ($result) {
                        $Id = $user->getId();
                    }
                    break;
                default:
                    break;
            }

            $CityID = new Address();
            $result= $CityID->read($addressID);
            $CityID = $CityID->getParentAddressID();

            $model = new Address(); 
            $addressList = [];

            if (!$model->GetWholeAddressesList($addressList)) {
                die("Failed to load address data.");
            }

            $data = [
                'Id' => $Id,
                'name' => trim($_POST["name"]),
                'age' => trim($_POST["age"]),
                'DistrictID' => $addressID,
                'CityID' => $CityID,
                'email' => $email,
                'role'=> $role,
                'addressList' => $addressList,
            ];

            require __DIR__ ."/../Views/Update.php";
        }
    }
    
}
?>