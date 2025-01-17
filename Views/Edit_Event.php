<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link rel="stylesheet" href="/Views/style_Home.css">

</head>
<body>
    <div class="container">
        <header>
            <h1>Edit Event</h1>
        </header>

        <section class="event-form-container">
            <h2>Update Event Details</h2>
            <form action="/../Controllers/EditEventController.php" method="POST">
                <!-- Hidden input for event ID -->
                <input type="hidden" name="id" value="<?= htmlspecialchars($event['id'] ?? '') ?>">

                <!-- Event Name -->
                <div class="form-group">
                    <label for="event-name">Name:</label>
                    <input type="text" id="event-name" name="name" value="<?= htmlspecialchars($event['name'] ?? '') ?>" required>
                </div>

                <!-- Address -->
                <div class="form-group">
                    <label for="event-address-city">Address:</label>
                    <select name="city" id="event-address-city" onchange="populateChildren()" class="input-field" required>
                        <option value="">-- Select a City --</option>
                        <?php foreach ($event['addressList'] as $parent): ?>
                            <option value="<?= $parent['ID']; ?>" <?= isset($event['CityID']) && $event['CityID'] == $parent['ID'] ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($parent['Name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- District Dropdown -->
                    <select name="region" id="event-address-region" class="input-field" required>
                        <option value="">-- Select a District --</option>
                        <?php 
                        // Populate the district dropdown based on the selected city
                        if (isset($event['CityID'])) {
                            foreach ($event['addressList'] as $parent) {
                                if ($parent['ID'] == $event['CityID'] && isset($parent['children'])) {
                                    foreach ($parent['children'] as $child) {
                                        ?>
                                        <option value="<?= $child['ID']; ?>" <?= isset($event['DistrictID']) && $event['DistrictID'] == $child['ID'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($child['Name']); ?>
                                        </option>
                                        <?php
                                    }
                                }
                            }
                        }
                        ?>
                    </select>
                </div>

                <!-- Date and Time -->
                <div class="form-group">
                    <label for="event-date">Date:</label>
                    <input type="date" id="event-date" name="date" value="<?= htmlspecialchars($event['date'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="event-time">Time:</label>
                    <input type="time" id="event-time" name="time" value="<?= htmlspecialchars($event['time'] ?? '') ?>" required>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="event-description">Description:</label>
                    <textarea id="event-description" name="description" required><?= htmlspecialchars($event['description'] ?? '') ?></textarea>
                </div>

                <!-- Max Attendees -->
                <div class="form-group">
                    <label for="max-attendees">Max Number of Attendees:</label>
                    <input type="number" id="max-attendees" name="max_attendees" value="<?= htmlspecialchars($event['max_attendees'] ?? '') ?>" required>
                </div>

                <!-- Type -->
                <div class="form-group">
                    <label for="event-type">Type:</label>
                    <select id="event-type" name="type" required>
                        <option value="Donation-Collect" <?= isset($event['type']) && $event['type'] == 'Donation-Collect' ? 'selected' : '' ?>>Donation Collect</option>
                        <option value="Medical-Tour" <?= isset($event['type']) && $event['type'] == 'Medical-Tour' ? 'selected' : '' ?>>Medical Tour</option>
                        <option value="Other" <?= isset($event['type']) && $event['type'] == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-button">Update Event</button>
            </form>
            <button onclick="location.href='/../Controllers/EventAdminHomeController.php'" class="back-button">Back to Dashboard</button>
        </section>
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
