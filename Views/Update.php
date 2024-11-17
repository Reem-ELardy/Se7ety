<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Se7ety - Update Data</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/Views/style_Signup.css">
</head>
<body>

  <div class="signup-container">
    <img src="/Views/Assets/logo-for-sdp.png" alt="Logo" class="logo">
    <h2>Update Data</h2>
    <form action="/Controllers/UpdateController.php" method="POST">
      <input type="hidden" name="Id" value="<?php echo $data['Id']; ?>">
      <input type="text" name="name" class="input-field" placeholder="Full Name" value="<?php echo isset($data['name']) ? htmlspecialchars($data['name']) : ''; ?>" required>
      <input type="text" name="age" class="input-field" placeholder="Age" value="<?php echo isset($data['age']) ? htmlspecialchars($data['age']) : ''; ?>" required>

      <!-- City Dropdown -->
      <select name="CityAdress" id="parentDropdown" onchange="populateChildren()" class="input-field" required>
        <option value="">-- Select a City --</option>
        <?php foreach ($data['addressList'] as $parent): ?>
            <option value="<?= $parent['ID']; ?>" <?= isset($data['CityID']) && $data['CityID'] == $parent['ID'] ? 'selected' : ''; ?>>
                <?= htmlspecialchars($parent['Name']); ?>
            </option>
        <?php endforeach; ?>
      </select>

      <!-- District Dropdown -->
      <select name="DistrictAdress" id="DistrictAdress" class="input-field" required>
        <option value="">-- Select a District --</option>
        <?php 
        // Populate the district dropdown based on the selected city
        if (isset($data['CityID'])) {
            foreach ($data['addressList'] as $parent) {
                if ($parent['ID'] == $data['CityID'] && isset($parent['children'])) {
                    foreach ($parent['children'] as $child) {
                        ?>
                        <option value="<?= $child['ID']; ?>" <?= isset($data['DistrictID']) && $data['DistrictID'] == $child['ID'] ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($child['Name']); ?>
                        </option>
                        <?php
                    }
                }
            }
        }
        ?>
      </select>

      <input type="email" name="email" class="input-field" placeholder="Email" value="<?php echo isset($data['email']) ? htmlspecialchars($data['email']) : ''; ?>" required>
      <input type="password" name="password" class="input-field" placeholder="Password" required>

      <span style="color:red;"><?php echo isset($error) ? htmlspecialchars($error) : ''; ?></span><br>

      <div class="radio-group">
        <label><strong>Role:</strong> <?php echo htmlspecialchars($data['role']); ?></label>
        <input type="hidden" name="role" value="<?php echo htmlspecialchars($data['role']); ?>">
      </div>

      <button type="submit" class="submit-btn">Update</button>
    </form>
  </div>

</body>

<script>
  // Address data passed from PHP
  const addressList = <?= json_encode($data['addressList']); ?>;
  const selectedDistrict = <?= json_encode($data['DistrictID'] ?? ''); ?>; // Ensure selectedDistrict is set

  function populateChildren() {
      const parentId = document.getElementById('parentDropdown').value;
      const childDropdown = document.getElementById('DistrictAdress');

      // Reset child dropdown
      childDropdown.innerHTML = '<option value="">-- Select a District --</option>';

      // Find the selected parent (city)
      const selectedParent = addressList.find(parent => parent.ID == parentId);

      // Populate child dropdown if children exist
      if (selectedParent && selectedParent.children) {
          selectedParent.children.forEach(child => {
              const option = document.createElement('option');
              option.value = child.ID;
              option.textContent = child.Name;

              // Pre-select the district if it matches
              if (child.ID == selectedDistrict) {
                  option.selected = true;
              }

              childDropdown.appendChild(option);
          });
      }
  }

  // Populate district dropdown on page load if a city is already selected
  document.addEventListener('DOMContentLoaded', () => {
      const selectedCity = document.getElementById('parentDropdown').value;
      if (selectedCity) {
          populateChildren();  // Populate the district options if city is selected
      }
  });
</script>

</html>
