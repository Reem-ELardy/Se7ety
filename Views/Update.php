<?php
session_start();
$passwordError = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = isset($_POST['password']) ? $_POST['password'] : '';  

    
    $passwordPattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/";

    
    if (!preg_match($passwordPattern, $password)) {
        $passwordError = "Password must contain at least 8 characters, a number, a letter, and a special character.";
    } else {
        // Update session data with new values
        $_SESSION['name'] = htmlspecialchars($_POST['name']);
        $_SESSION['age'] = htmlspecialchars($_POST['age']);
        $_SESSION['address'] = htmlspecialchars($_POST['address']);
        $_SESSION['email'] = htmlspecialchars($_POST['email']);
        $_SESSION['role'] = htmlspecialchars($_POST['role']);
        
        // Redirect to display page
        header("Location: display.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Se7ety - Update Data</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style_Signup.css">
</head>
<body>

  <div class="signup-container">
    <img src="./Assets/logo-for-sdp.png" alt="Logo" class="logo">
    <h2>Update Data</h2>
    <form action="" method="POST">
      <input type="text" name="name" class="input-field" placeholder="Full Name" value="<?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : ''; ?>" required>
      <input type="text" name="age" class="input-field" placeholder="Age" value="<?php echo isset($_SESSION['age']) ? htmlspecialchars($_SESSION['age']) : ''; ?>" required>
      <input type="text" name="address" class="input-field" placeholder="Address" value="<?php echo isset($_SESSION['address']) ? htmlspecialchars($_SESSION['address']) : ''; ?>" required>
      <input type="email" name="email" class="input-field" placeholder="Email" value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" required>
      <input type="password" name="password" class="input-field" placeholder="Password" required>
      <span style="color:red;"><?php echo $passwordError; ?></span><br>

      <div class="radio-group">
        <label><strong>Role:</strong> <?php echo htmlspecialchars($_SESSION['role']); ?></label>
        <input type="hidden" name="role" value="<?php echo htmlspecialchars($_SESSION['role']); ?>">
      </div>

      <button type="submit" class="submit-btn">Update</button>
    </form>
  </div>

</body>
</html>
