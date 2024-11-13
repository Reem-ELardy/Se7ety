<?php
  $name = htmlspecialchars($_POST['name']);
  echo "<h1>User $name has been deleted.</h1>";
  echo "<a href='signup.php'>Back to Signup</a>";
?>
