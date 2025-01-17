<?php
require_once 'LoginController.php';

session_unset();
session_destroy();

require __DIR__ ."/../Views/Login.php";
header("Location: http://localhost:3000/Views/Login.php");

?>