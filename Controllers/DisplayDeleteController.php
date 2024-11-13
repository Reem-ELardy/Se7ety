<?php
require_once 'UserController.php';  // Include the SignupController
$role = $_POST["role"];
$email = $_POST["email"];

$controller = new UserController();
$controller->Delete($email, $role);  // Call the validation method
?>