<?php
session_start(); 
$passwordError = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    
    
    $passwordPattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/";
    
   
    if (!preg_match($passwordPattern, $password)) {
        $passwordError = "Password must contain at least 8 characters, a number, a letter, and a special character.";
    } else {
       
        $_SESSION['name'] = htmlspecialchars($_POST['name']);
        $_SESSION['age'] = htmlspecialchars($_POST['age']);
        $_SESSION['address'] = htmlspecialchars($_POST['address']);
        $_SESSION['email'] = htmlspecialchars($_POST['email']);
        $_SESSION['role'] = htmlspecialchars($_POST['role']);
        
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
  <title>Se7ety - Signup</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style_Signup.css">
</head>
<body>

  <div class="signup-container">
    <img src="./Assets/logo-for-sdp.png" alt="Logo" class="logo">
    <h2>Signup</h2>
    
    <form action="/Controllers/SignupController.php" method="POST">
      <input type="text" name="name" class="input-field" placeholder="Full Name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
      <input type="text" name="age" class="input-field" placeholder="Age" value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>" required>
      <!-- Address Dropdown -->
      <select name="address" class="input-field" required>
          <option value="">Select City</option>
          <option value="Address1" <?php if (isset($_POST['address']) && $_POST['address'] == 'Address1') echo 'selected'; ?>>Address1</option>
          <option value="Address2" <?php if (isset($_POST['address']) && $_POST['address'] == 'Address2') echo 'selected'; ?>>Address2</option>
          <option value="Address3" <?php if (isset($_POST['address']) && $_POST['address'] == 'Address3') echo 'selected'; ?>>Address3</option>
          <!-- Add more options as needed -->
      </select>
      <select name="address" class="input-field" required>
          <option value="">Select Region</option>
          <option value="Address1" <?php if (isset($_POST['address']) && $_POST['address'] == 'Address1') echo 'selected'; ?>>Address1</option>
          <option value="Address2" <?php if (isset($_POST['address']) && $_POST['address'] == 'Address2') echo 'selected'; ?>>Address2</option>
          <option value="Address3" <?php if (isset($_POST['address']) && $_POST['address'] == 'Address3') echo 'selected'; ?>>Address3</option>
          <!-- Add more options as needed -->
      </select>

      <input type="email" name="email" class="input-field" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
      <input type="password" name="password" class="input-field" placeholder="Password" value="<?php echo isset($_POST['password']) ? htmlspecialchars($_POST['password']) : ''; ?>" required>
      <span style="color:red;"><?php echo $passwordError; ?></span><br>

      <div class="radio-group">
        <label><input type="radio" name="role" value="Volunteer" <?php if (isset($_POST['role']) && $_POST['role'] == 'Volunteer') echo 'checked'; ?> required> I am a Volunteer</label>
        <label><input type="radio" name="role" value="Donor" <?php if (isset($_POST['role']) && $_POST['role'] == 'Donor') echo 'checked'; ?>> I am a Donor</label>
        <label><input type="radio" name="role" value="Patient" <?php if (isset($_POST['role']) && $_POST['role'] == 'Patient') echo 'checked'; ?>> I am a Patient</label>
      </div>

      <button type="submit" class="submit-btn">Sign up</button>
    </form>

    <a href="Login.php" class="login-link">Already have an account? Login</a>
  </div>

</body>
</html>
