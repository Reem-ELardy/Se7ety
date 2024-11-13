<?php


require_once __DIR__ . '/../Design Pattren/PersonFactory.php';


class UserController {


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
