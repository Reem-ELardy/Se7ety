

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Se7ety - Signup</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/Views/style_Signup.css">
</head>
<body>

  <div class="signup-container">
    <img src="/Views/Assets/logo-for-sdp.png" alt="Logo" class="logo">
    <h2>Signup</h2>
    
    <form action="/Controllers/SignupController.php" method="POST">
      <input type="text" name="name" class="input-field" placeholder="Full Name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
      <input type="text" name="age" class="input-field" placeholder="Age" value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>" required>
      <!-- Address Dropdown -->

      <!-- City Dropdown -->
      <select  name="CityAdress" id="parentDropdown" onchange="populateChildren()"  class="input-field" required>
        <option value="">-- Select a City --</option>
        <?php foreach ($addressList as $parent): ?>
            <option value="<?= $parent['ID']; ?>"><?= $parent['Name']; ?></option>
        <?php endforeach; ?>
    </select>

      <!-- District Dropdown -->
      <select name="DistrictAdress" id="DistrictAdress" class="input-field" required>
        
        <option value="">-- Select a District --</option>
    </select>

      <input type="email" name="email" class="input-field" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
      <input type="password" name="password" class="input-field" placeholder="Password" value="<?php echo isset($_POST['password']) ? htmlspecialchars($_POST['password']) : ''; ?>" required>
      <span style="color:red;"><?php echo isset($error) ? htmlspecialchars($error) : ''; ?></span><br>

      <div class="radio-group">
        <label><input type="radio" name="role" value="Volunteer" <?php if (isset($_POST['role']) && $_POST['role'] == 'Volunteer') echo 'checked'; ?> required> I am a Volunteer</label>
        <label><input type="radio" name="role" value="Donor" <?php if (isset($_POST['role']) && $_POST['role'] == 'Donor') echo 'checked'; ?>> I am a Donor</label>
        <label><input type="radio" name="role" value="Patient" <?php if (isset($_POST['role']) && $_POST['role'] == 'Patient') echo 'checked'; ?>> I am a Patient</label>
      </div>

      <button type="submit" class="submit-btn">Sign up</button>
    </form>

    <a href="../Views/Login.php" class="login-link">Already have an account? Login</a>
  </div>

</body>
<script>
  
  const addressList = <?= json_encode($data['addressList']); ?>;
    function populateChildren() {
      const parentId = document.getElementById('parentDropdown').value;
      const childDropdown = document.getElementById('DistrictAdress');

      // Reset child dropdown
      childDropdown.innerHTML = '<option value="">-- Select a District --</option>';

      // Debugging logs
      console.log("Selected Parent ID:", parentId);

      // Find the selected parent
      const selectedParent = addressList.find(parent => parent.ID == parentId);

      // If parent exists and has children, populate the child dropdown
      if (selectedParent && selectedParent.children) {
        selectedParent.children.forEach(child => {
          const option = document.createElement('option');
          option.value = child.ID;
          option.textContent = child.Name;
          childDropdown.appendChild(option);
        });
      } else {
          console.log("No children found for selected parent.");
      }
  }


  </script>
</html>
