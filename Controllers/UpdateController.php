<?php
    require_once 'UserController.php';  // Include the SignupController

    $controller = new UserController();
    $controller->updateUser();  // Call the validation method
?>