<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Se7ety - Login</title>
  <link rel="stylesheet" href="style_Login.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

</head>
<body>

  <div class="login-container">
	<img src="./Assets/logo-for-sdp.png" alt="Logo" class="logo">
    <h2>Login</h2>
    <form action="/Controllers/LoginController.php" method="POST">
      <input type="email" class="input-field" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"  required>
      <input type="password" class="input-field" placeholder="Password" value="<?php echo isset($_POST['password']) ? htmlspecialchars($_POST['password']) : ''; ?>" required>


      <div class="radio-group">
              <label><input type="radio" name="role" value="Volunteer" <?php if (isset($_POST['role']) && $_POST['role'] == 'Volunteer') echo 'checked'; ?> required> I am a Volunteer</label>
              <label><input type="radio" name="role" value="Donor" <?php if (isset($_POST['role']) && $_POST['role'] == 'Donor') echo 'checked'; ?>> I am a Donor</label>
              <label><input type="radio" name="role" value="Donor" <?php if (isset($_POST['role']) && $_POST['role'] == 'Patient') echo 'checked'; ?>> I am a Patient</label>
      </div>


      <button type="submit" class="submit-btn">Login</button>
      <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif ?>
      
    </form>
    <a href="signup.php" class="signup-link">Don't have an account? Sign up</a>
  </div>

</body>
</html>
