<?php
require_once 'UserController.php';  // Include the SignupController

$controller = new UserController();
$controller->LoginValidation();  // Call the validation method
?>