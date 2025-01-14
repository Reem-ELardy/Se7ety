<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Details</title>
    <link rel="stylesheet" href="/Views/style_Home.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Donation Details</h1>
        </header>

        <section id="donation-details">
            <h2>Details</h2>
            <div id="donation-info">Loading donation details...</div>
        </section>

        <button onclick="location.href='/../Controllers/GoingToHome.php'" class="back-button">Back to Home</button>
    </div>

    <script>
        const donationDetail = <?= json_encode($data['donationDetails']); ?>;
        const donationInfo = document.getElementById("donation-info");

        if (donationDetail) {
            donationInfo.innerHTML = "";

            // Display Money Donation Details
            if (donationDetail['type'] === 'Money' || donationDetail['type'] === 'Both') {
                donationInfo.innerHTML += `
                    <div class="donation-section">
                        <h3>Money Donation</h3>
                        <strong>Amount:</strong> ${donationDetail['cashamount']}<br>
                    </div>
                `;
            }

            // Display Medical Donation Details
            if (donationDetail['type'] === 'Medical' || donationDetail['type'] === 'Both') {
                donationInfo.innerHTML += `
                    <div class="donation-section">
                        <h3>Medical Donations</h3>
                `;

                const medicalItems = donationDetail['Items'];

                if (Array.isArray(medicalItems) && medicalItems.length > 0) {
                    medicalItems.forEach((item, index) => {
                        donationInfo.innerHTML += `
                            <strong>Medical Donation ${index + 1}:</strong><br>
                            <strong>Name:</strong> ${item['medicalname']}<br>
                            <strong>Type:</strong> ${item['medicaltype']}<br>
                            <strong>Quantity:</strong> ${item['quantity']}<br>
                            <hr>
                        `;
                    });
                } else {
                    donationInfo.innerHTML += "No medical items available.<br>";
                }

                donationInfo.innerHTML += `</div>`;
            }
        } else {
            donationInfo.textContent = "Donation not found.";
        }
    </script>
</body>
</html>
