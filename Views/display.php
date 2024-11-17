<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Details</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/Views/style_Display.css">
</head>
<body>

<div class="details-container">
    <?php if ($deleted): ?>
        <!-- Display success message if user data is deleted -->
        <h2>User Deleted Successfully</h2>
        <p>The user data has been removed.</p>
        <a href="/Controllers/SignupController.php" class="back-link">Back to Signup</a>
    <?php else: ?>
        <!-- Display user details if data is available -->
        <h2>User Details</h2>
        <ul class="details-list">
          <li><strong>Full Name:</strong> <?php echo $_SESSION['name']; ?></li>
          <li><strong>Age:</strong> <?php echo $_SESSION['age']; ?></li>
          <li><strong>Address:</strong> <?php echo $_SESSION['wholeAddress']; ?></li>
          <li><strong>Email:</strong> <?php echo $_SESSION['email']; ?></li>
          <li><strong>Role:</strong> <?php echo $_SESSION['role']; ?></li>
        </ul>

        <div class="button-group">
          <!-- Update Button -->
          <form action="DisplayUpdateController.php" method="POST" style="display:inline;">
            <input type="hidden" name="name" value="<?php echo $_SESSION['name']; ?>">
            <input type="hidden" name="age" value="<?php echo $_SESSION['age']; ?>">
            <input type="hidden" name="AddressId" value="<?php echo $_SESSION['AddressId']; ?>">
            <input type="hidden" name="email" value="<?php echo $_SESSION['email']; ?>">
            <input type="hidden" name="role" value="<?php echo $_SESSION['role']; ?>">
            <button type="submit" class="update-btn">Update</button>
          </form>

          <!-- Delete Button -->
          <form action="DisplayDeleteController.php" method="POST" style="display:inline;">
            <input type="hidden" name="email" value="<?php echo $_SESSION['email']; ?>">
            <input type="hidden" name="role" value="<?php echo $_SESSION['role']; ?>">
            <input type="hidden" name="delete" value="true">
            <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
          </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
