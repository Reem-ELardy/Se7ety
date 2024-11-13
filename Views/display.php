<?php
session_start();
$deleted = false;


if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['delete'])) {
    // Update session data with posted values
    //this is the part that you will put on it id part
    $_SESSION['name'] = $_POST['name'];
    $_SESSION['age'] = $_POST['age'];
    $_SESSION['address'] = $_POST['address'];
    $_SESSION['email'] = $_POST['email'];
    $_SESSION['role'] = $_POST['role'];
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    session_unset();
    session_destroy();
    $deleted = true;
}


if (isset($_SESSION['name']) && isset($_SESSION['age']) && isset($_SESSION['address']) && isset($_SESSION['email']) && isset($_SESSION['role'])) {
    $name = $_SESSION['name'];
    $age = $_SESSION['age'];
    $address = $_SESSION['address'];
    $email = $_SESSION['email'];
    $role = $_SESSION['role'];
} else if (!$deleted) {
    header("Location: signup.php");
    exit();
}
?>


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
    <?php if ($deleted): ?>
        <!-- Display success message if user data is deleted -->
        <h2>User Deleted Successfully</h2>
        <p>The user data has been removed.</p>
        <a href="signup.php" class="back-link">Back to Signup</a>
    <?php else: ?>
        <!-- Display user details if data is available -->
        <h2>User Details</h2>
        <ul class="details-list">
          <li><strong>Full Name:</strong> <?php echo $name; ?></li>
          <li><strong>Age:</strong> <?php echo $age; ?></li>
          <li><strong>Address:</strong> <?php echo $address; ?></li>
          <li><strong>Email:</strong> <?php echo $email; ?></li>
          <li><strong>Role:</strong> <?php echo $role; ?></li>
        </ul>

        <div class="button-group">
          <!-- Update Button -->
          <form action="update.php" method="POST" style="display:inline;">
            <input type="hidden" name="name" value="<?php echo $name; ?>">
            <input type="hidden" name="age" value="<?php echo $age; ?>">
            <input type="hidden" name="address" value="<?php echo $address; ?>">
            <input type="hidden" name="email" value="<?php echo $email; ?>">
            <input type="hidden" name="role" value="<?php echo $role; ?>">
            <button type="submit" class="update-btn">Update</button>
          </form>

          <!-- Delete Button -->
          <form action="display.php" method="POST" style="display:inline;">
            <input type="hidden" name="delete" value="true">
            <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
          </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
