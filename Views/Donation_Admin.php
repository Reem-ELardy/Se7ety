<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Donation Admin</title>
        <link rel="stylesheet" href="/Views/style_Home.css">
    </head>
    <body>
        <div class="container">
            <header>
                <a href="/Controllers/LogOutController.php" class="back-button">logout</a>
                <h1>Donation Admin</h1>
            </header>

            <section class="donation-list">
                <h2>Donations</h2>
                <div id="donations-container">
                    <!-- Dynamic list of donations will appear here -->
                </div>
            </section>
        </div>

        <script>
            const donations = <?= json_encode($data['Donations'] ?? null); ?>;
            console.log(donations)


            const donationsContainer = document.getElementById("donations-container");

            // Render donations dynamically
            donations.forEach(donation => {
                const donationItem = document.createElement("div");
                donationItem.classList.add("donation-item");

                // Donation details
                let donationDetails = "";
                if (donation.Type === "Money") {
                    donationDetails = `<p>Type: Money Donation</p><p>Amount: $${donation.Cashamount}</p>`;
                } else if (donation.Type === "Medical") {
                    donationDetails = `<p>Type: Medical Donation</p><p>Quantity: ${donation.Quantity}</p>`;
                }

                donationItem.innerHTML = `
                    <div class="donation-details">
                        ${donationDetails}
                        <p>Status: ${donation.Status}</p>
                    </div>
                `;

                // Add "Action" button dynamically if status is pending
                if (donation.Status === "Pending") {
                    const actionButton = document.createElement("button");
                    actionButton.classList.add("action-button");
                    actionButton.textContent = "Completed";
                    actionButton.addEventListener("click", () => {
                        window.location.href = `/../Controllers/DonationAdmin.php?id=${donation.id}`;
                    });
                    donationItem.appendChild(actionButton);
                }

                donationsContainer.appendChild(donationItem);
            });
        </script>
    </body>
</html>
