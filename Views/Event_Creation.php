<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
    <link rel="stylesheet" href="/Views/style_Home.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Create Event</h1>
        </header>

        <section class="event-form-container">
            <h2>Event Details</h2>
            <form action="/../Controllers/EventCreationController.php" method="POST">
                <!-- Event Name -->
                <div class="form-group">
                    <label for="event-name">Name:</label>
                    <input type="text" id="event-name" name="name" placeholder="Enter event name" required>
                </div>

                <!-- Address Dropdown -->

                <!-- City Dropdown -->
                <div class="form-group">
                <label for="event-address-city">Address1:</label>
                    <select  name="city" id="event-address-city" onchange="populateChildren()"  class="input-field" required>
                        <option value="">-- Select a City --</option>
                        <?php foreach ($addressList as $parent): ?>
                            <option value="<?= $parent['ID']; ?>"><?= $parent['Name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="event-address-city">Address2:</label>
                    <!-- District Dropdown -->
                    <select name="region" id="event-address-region" class="input-field" required>
                        <option value="">-- Select a Region --</option>
                    </select>
                </div>
                <!-- Address
                <div class="form-group">
                    <label for="event-address-city">Address1:</label>
                    <select name="region" id="event-address-city" class="input-field" required>
                        <option value="">Select City</option>
                        <option value="City1">City1</option>
                        <option value="City2">City2</option>
                        <option value="City3">City3</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="event-address-city">Address2:</label>
                    <select name="region" id="event-address-region" class="input-field" required>
                        <option value="">Select Region</option>
                        <option value="Region1">Region1</option>
                        <option value="Region2">Region2</option>
                        <option value="Region3">Region3</option>
                    </select>
                </div> -->

                <!-- Date and Time -->
                <div class="form-group">
                    <label for="event-date">Date:</label>
                    <input type="date" id="event-date" name="date" required>
                </div>
                <div class="form-group">
                    <label for="event-time">Time:</label>
                    <input type="time" id="event-time" name="time" required>
                </div>

                <!-- Description -->
                <div class="form-group">
                    <label for="event-description">Description:</label>
                    <textarea id="event-description" name="description" placeholder="Enter event description" required></textarea>
                </div>

                <!-- Max Attendees -->
                <div class="form-group">
                    <label for="max-attendees">Max Number of Attendees:</label>
                    <input type="number" id="max-attendees" name="max_attendees" min="1" placeholder="Enter max number of attendees" required>
                </div>

                <!-- Type -->
                <div class="form-group">
                    <label for="event-type">Type:</label>
                    <select id="event-type" name="type" required>
                        <option value="">Select Type</option>
                        <option value="Donation-Collect">Donation Collect</option>
                        <option value="Medical-Tour">Medical Tour</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-button">Create Event</button>
            </form>
            <button onclick="location.href='EventAdminHomeController.php'" class="back-button">Back to Admin Dashboard</button>
        </section>
    </div>
</body>
<script>
    
  const addressList = <?= json_encode($data['addressList']); ?>;
    function populateChildren() {
      const parentId = document.getElementById('event-address-city').value;
      const childDropdown = document.getElementById('event-address-region');

      // Reset child dropdown
      childDropdown.innerHTML = '<option value="">-- Select a City --</option>';

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
