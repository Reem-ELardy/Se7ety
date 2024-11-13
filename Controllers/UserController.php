<?php


require_once __DIR__ . '/../Design Pattren/PersonFactory.php';


class UserController {

    public function SignupValidation() {
        $PersonFactory=new PersonFactory();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = trim($_POST["name"]);
            $age = trim($_POST["age"]);
            $email = $_POST["email"];
            $password = $_POST["password"];
            $role = $_POST["role"];
            $error = "";

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

            if (empty($error)) {
                print("\nI am here");
                print($role);
                $result = $PersonFactory->SignupFactory($name, $age, $email, $password, $role);
                // $result = $this->Signup($name, $age, $email, $password, $role);
                //print($result);

                if ($result == false) {
                    
                    $error = "This user already exists.";
                    header("Location:/../Views\Login.php");
                    // exit();
                } else {
                    // $error = "This user already exists.";
                    // header("Location: Login.php");
                    // exit();
                }
            }
        }
        //require_once __DIR__ . '/../Views/Signup.php';
    }



    public function LoginValidation() {
        $PersonFactory= new PersonFactory();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = $_POST["email"];
            print("\nDone");
            $password = $_POST["password"];
            print("\nDone");
            $role = $_POST["role"];
            $error = "";

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email, please enter it again.";
            }


            if (empty($error)) {
              $result = $PersonFactory->LoginFactory($email, $password, $role);
             
              print("\nDone");

               
                if ($result != NULL) {
                    print("\nDone");
                    $view= "display";
                   
                    $this->Display($result,$role);
                } else {
                    $error = "Invalid credentials, please try again.";
                    header("Location:/../Views\Login.php");
                }
            }
        }
      
    }

 

    public function Display($result, $role){
        $data = [
            'name' => $result-> getName(),
            'age' => $result->getAge(),
            //'address' => $result[ 'address'],
            'email' => $result ->getEmail(),
            'role'=> $role,
           
        ];


        require __DIR__ ."/../Views/display.php";
    }

    public function updateUser() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = trim($_POST["name"]);
            $age = trim($_POST["age"]);
            $email = $_POST["email"];
            $password = $_POST["password"];
            $role = $_POST["role"];

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


            // Instantiate the correct class based on the role and set the attributes
            switch ($role) {
                case 'Volunteer':
                    $user = new Volunteer();
                    $user->setName($name);
                    $user->setAge($age);
                    $user->setEmail($email);
                    $user->setPassword($password);
                    // Call additional setters if there are more role-specific attributes
                    $result = $user->updateVolunteer();
                    break;
                case 'Donor':
                    $user = new Donor();
                    $user->setName($name);
                    $user->setAge($age);
                    $user->setEmail($email);
                    $user->setPassword($password);
                    $result = $user->updateDonor();
                    break;
                case 'Patient':
                    $user = new Patient();
                    $user->setName($name);
                    $user->setAge($age);
                    $user->setEmail($email);
                    $user->setPassword($password);
                    $result = $user->updatePatient();
                    break;
            }
        
            // Check if the update was successful
            if ($result) {
                echo "Update successful!";
                // Optionally, redirect back to the display view or another page
            } else {
                echo "Update failed.";
            }
        }
    }



    public function Delete($email, $role){
         //get user name
    
        switch ($role) {
            case 'Volunteer':
                $user = new Volunteer();
                $result = $user->findByEmail($email);
                $result = $user->delete($user->getId());
                break;
            case 'Donor':
                $user = new Donor();
                $result = $user->findByEmail($email);
                $result = $user->delete($user->getId());
                break;
            case 'Patient':
                $user = new Patient();
                $result = $user->findByEmail($email);
                $result = $user->delete($user->getId());
                break;
            default:
                break;
        }

        require __DIR__ ."/../Views/delete.php";
    }
    
    
}
?>
