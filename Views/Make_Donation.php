<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Donation</title>
    <link rel="stylesheet" href="/Views/style_Home.css">
</head>
<body>
    <div class="container">
        <header>
            <a href="/../Controllers/GoingToHome.php" class="back-button">Back</a>
            <h1>Make a Donation</h1>
        </header>

        <section class="donation-options">
            <h2>Select Donation Type</h2>
            <form action="/Controllers/Make_DonationController.php" method="POST" id="donation-form">
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="donation-type" value="money" id="money-checkbox">
                        Money Donation
                    </label>
                    <label>
                        <input type="checkbox" name="donation-type" value="medical" id="medical-checkbox">
                        Medical Donation
                    </label>
                </div>

                <!-- Money Donation Section -->
                <div id="money-donation-section" style="display: none;">
                    <h3>Money Donation</h3>
                    <div class="form-group">
                        <label for="money_amount">Amount ($):</label>
                        <input type="number" id="money_amount" name="money_amount" class="input-field" placeholder="Enter amount">
                    </div>
                </div>

                <!-- Medical Donation Section -->
                <div id="medical-donation-section" style="display: none;">
                    <div id="medical-donation-list">
                        <div class="medical-donation-item">
                            <h3>Medical Donation 1</h3>
                            <div class="form-group">
                                <label for="medical_name">Name:</label>
                                <input type="text" name="medical_name[]" class="input-field" placeholder="Enter name">
                            </div>
                            <div class="form-group">
                                <label for="medical_type">Type:</label>
                                <input type="text" name="medical_type[]" class="input-field" placeholder="Enter type">
                            </div>
                            <div class="form-group">
                                <label for="medical_quantity">Quantity:</label>
                                <input type="number" name="medical_quantity[]" class="input-field" placeholder="Enter quantity">
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-medical-donation" class="donate-button">Add Another Medical Donation</button>
                </div>

                <button type="submit" class="donate-button">Submit Donation</button>
            </form>
        </section>
    </div>

    <script>
        // Toggle display for donation sections
        const moneyCheckbox = document.getElementById('money-checkbox');
        const medicalCheckbox = document.getElementById('medical-checkbox');
        const moneySection = document.getElementById('money-donation-section');
        const medicalSection = document.getElementById('medical-donation-section');

        moneyCheckbox.addEventListener('change', () => {
            moneySection.style.display = moneyCheckbox.checked ? 'block' : 'none';
        });

        medicalCheckbox.addEventListener('change', () => {
            medicalSection.style.display = medicalCheckbox.checked ? 'block' : 'none';
        });

        // Add dynamic medical donation fields with titles
        const addMedicalDonationButton = document.getElementById('add-medical-donation');
        const medicalDonationList = document.getElementById('medical-donation-list');
        let medicalDonationCount = 1; // Counter for medical donations

        addMedicalDonationButton.addEventListener('click', () => {
            medicalDonationCount++; // Increment the counter

            const newDonationItem = document.createElement('div');
            newDonationItem.classList.add('medical-donation-item');
            newDonationItem.innerHTML = `
                <h3>Medical Donation ${medicalDonationCount}</h3>
                <div class="form-group">
                    <label for="medical_name">Name:</label>
                    <input type="text" name="medical_name[]" class="input-field" placeholder="Enter name">
                </div>
                <div class="form-group">
                    <label for="medical_type">Type:</label>
                    <input type="text" name="medical_type[]" class="input-field" placeholder="Enter type">
                </div>
                <div class="form-group">
                    <label for="medical_quantity">Quantity:</label>
                    <input type="number" name="medical_quantity[]" class="input-field" placeholder="Enter quantity">
                </div>
            `;
            medicalDonationList.appendChild(newDonationItem);
        });
    </script>
</body>
</html>
