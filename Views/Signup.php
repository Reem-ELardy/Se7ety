<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se7ety - Signup</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/View/style_Signup.css">
    <style>
      .hidden {
        display: none;
      }
    </style>
  </head>
  <body>
    <div class="signup-container">
      <img src="/Views/Assets/logo-for-sdp.png" alt="Logo" class="logo">
      <h2>Signup</h2>
      
      <form action="/Controllers/SignupController.php" method="POST">
        <!-- Full Name -->
        <input type="text" name="name" class="input-field" placeholder="Full Name" required>

        <!-- Age -->
        <input type="text" name="age" class="input-field" placeholder="Age" required>

        <!-- Phone Number -->
        <input type="text" name="phone" class="input-field" placeholder="Phone Number" required>

        <!-- City Dropdown -->
        <select name="CityAdress" id="parentDropdown" onchange="populateChildren()" class="input-field" required>
          <option value="">-- Select a City --</option>
          <?php foreach ($addressList as $parent): ?>
              <option value="<?= $parent['ID']; ?>"><?= $parent['Name']; ?></option>
          <?php endforeach; ?>
        </select>

        <!-- District Dropdown -->
        <select name="DistrictAdress" id="DistrictAdress" class="input-field" required>
          <option value="">-- Select a District --</option>
        </select>

        <!-- Email -->
        <input type="email" name="email" class="input-field" placeholder="Email" required>
        
        <!-- Password -->
        <input type="password" name="password" class="input-field" placeholder="Password" required>
        
        <span style="color:red;"><?php echo isset($error) ? htmlspecialchars($error) : ''; ?></span><br>

        <!-- Role Selection -->
        <div class="radio-group">
          <label>
            <input type="radio" name="role" value="Volunteer" onchange="toggleVolunteerFields()"> I am a Volunteer
          </label>
          <label>
            <input type="radio" name="role" value="Donor" onchange="toggleVolunteerFields()"> I am a Donor
          </label>
          <label>
            <input type="radio" name="role" value="Patient" onchange="toggleVolunteerFields()"> I am a Patient
          </label>
        </div>

        <!-- Volunteer Job Dropdown -->
        <div id="volunteer-job-dropdown" class="hidden">
          <select name="volunteerJob" class="input-field">
            <option value="">-- Select Job --</option>
            <option value="Doctor">Doctor</option>
            <option value="Nurse">Nurse</option>
            <option value="Other">Other</option>
          </select>
        </div>

        <!-- Volunteer Skills Dropdown -->
        <div id="volunteer-skill-dropdown" class="hidden">
          <select name="volunteerSkill" class="input-field" id="volunteerSkill">
            <option value="">-- Select Skill --</option>
            <!-- Skills will be dynamically populated via JavaScript -->
          </select>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="submit-btn">Sign up</button>
      </form>

      <!-- Login Link -->
      <a href="../Views/Login.php" class="login-link">Already have an account? Login</a>
    </div>

    <script>
      // Populate District Dropdown
      const addressList = <?= json_encode($data['addressList']); ?>;
      function populateChildren() {
        const parentId = document.getElementById('parentDropdown').value;
        const childDropdown = document.getElementById('DistrictAdress');

        childDropdown.innerHTML = '<option value="">-- Select a District --</option>';

        const selectedParent = addressList.find(parent => parent.ID == parentId);

        if (selectedParent && selectedParent.children) {
          selectedParent.children.forEach(child => {
            const option = document.createElement('option');
            option.value = child.ID;
            option.textContent = child.Name;
            childDropdown.appendChild(option);
          });
        }
      }

      // Populate Volunteer Skills Dropdown
      const skillList = <?= json_encode($data['skillList']); ?>;
      function populateSkills() {
        const skillDropdown = document.getElementById('volunteerSkill');

        skillDropdown.innerHTML = '<option value="">-- Select Skill --</option>';
        skillList.forEach(skill => {
          const option = document.createElement('option');
          option.value = skill.id; // Skill ID as the value
          option.textContent = skill.name; // Skill Name as the display text
          option.id = `skill-${skill.id}`; // Assigning unique ID to the option
          skillDropdown.appendChild(option);
        });
      }

      // Toggle Volunteer Fields (Jobs and Skills)
      function toggleVolunteerFields() {
        const volunteerRadio = document.querySelector('input[name="role"][value="Volunteer"]');
        const jobDropdown = document.getElementById('volunteer-job-dropdown');
        const skillDropdown = document.getElementById('volunteer-skill-dropdown');

        if (volunteerRadio && volunteerRadio.checked) {
          jobDropdown.classList.remove('hidden');
          skillDropdown.classList.remove('hidden');
          populateSkills();
        } else {
          jobDropdown.classList.add('hidden');
          skillDropdown.classList.add('hidden');
        }
      }

      // Initialize state on page load
      document.addEventListener('DOMContentLoaded', () => {
        toggleVolunteerFields();
      });
    </script>
  </body>
</html>
