<?php
    require_once 'UserController.php';  // Include the SignupController

    $controller = new UserController();
    $controller->SignupValidation();  // Call the validation method
?>