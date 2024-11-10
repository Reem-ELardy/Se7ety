<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Details</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style_Display.css">
</head>
<body>

<div class="details-container">
    <h2>User Details</h2>
    <ul class="details-list">
      <li><strong>Full Name:</strong> <?php echo htmlspecialchars($_POST['name']); ?></li>
      <li><strong>Age:</strong> <?php echo htmlspecialchars($_POST['age']); ?></li>
      <li><strong>Address:</strong> <?php echo htmlspecialchars($_POST['address']); ?></li>
      <li><strong>Email:</strong> <?php echo htmlspecialchars($_POST['email']); ?></li>
      <li><strong>Role:</strong> <?php echo htmlspecialchars($_POST['role']); ?></li>
    </ul>

    <div class="button-group">
      <!-- Update Button -->
      <form action="Update.php" method="POST" style="display:inline;">
        <input type="hidden" name="name" value="<?php echo htmlspecialchars($_POST['name']); ?>">
        <input type="hidden" name="age" value="<?php echo htmlspecialchars($_POST['age']); ?>">
        <input type="hidden" name="address" value="<?php echo htmlspecialchars($_POST['address']); ?>">
        <input type="hidden" name="email" value="<?php echo htmlspecialchars($_POST['email']); ?>">
        <input type="hidden" name="role" value="<?php echo htmlspecialchars($_POST['role']); ?>">
        <button type="submit" class="update-btn">Update</button>
      </form>

      <!-- Delete Button -->
      <form action="delete.php" method="POST" style="display:inline;">
        <input type="hidden" name="name" value="<?php echo htmlspecialchars($_POST['name']); ?>">
        <button type="submit" class="delete-btn">Delete</button>
      </form>
    </div>

    <a href="signup.php" class="back-link">Back to Signup</a>
  </div>


</body>
</html>
