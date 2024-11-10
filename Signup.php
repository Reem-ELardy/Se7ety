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
    <form action="display.php" method="POST">
            <input type="text" name="name" class="input-field" placeholder="Full Name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            <input type="text" name="age" class="input-field" placeholder="Age" value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>" required>
            <input type="text" name="address" class="input-field" placeholder="Address" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>" required>
            <input type="email" name="email" class="input-field" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            <input type="password" name="password" class="input-field" placeholder="Password" required>

            <div class="radio-group">
              <label><input type="radio" name="role" value="Volunteer" <?php if (isset($_POST['role']) && $_POST['role'] == 'Volunteer') echo 'checked'; ?> required> I am a Volunteer</label>
              <label><input type="radio" name="role" value="Donor" <?php if (isset($_POST['role']) && $_POST['role'] == 'Donor') echo 'checked'; ?>> I am a Donor</label>
              <label><input type="radio" name="role" value="Donor" <?php if (isset($_POST['role']) && $_POST['role'] == 'Patient') echo 'checked'; ?>> I am a Patient</label>
            </div>

            <button type="submit" class="submit-btn">Sign up</button>
    </form>
    <a href="Login.php" class="login-link">Already have an account? Login</a>
  </div>

</body>
</html>
