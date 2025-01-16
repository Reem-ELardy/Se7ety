<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates</title>
    <link rel="stylesheet" href="style_Home.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Volunteer Certificates</h1>
        </header>

        <section class="certificates-container">
            <h2>Total Volunteering Hours</h2>
            <p id="total-hours">Loading...</p>

            <h2>Certificates</h2>
            <ul class="certificates-list" id="certificates-list">
                <!-- Certificates will be dynamically loaded here -->
            </ul>
        </section>

        <button onclick="location.href='Volunteer_Dashboard.php'" class="back-button">Back to Dashboard</button>
    </div>

    <script>
        // Example data for testing (replace with dynamic data from the controller)
        const volunteerData = <?= json_encode($data['volunteerDetails'] ?? null); ?>;

        const totalHoursElement = document.getElementById("total-hours");
        const certificatesListElement = document.getElementById("certificates-list");

        if (volunteerData) {
            // Display total hours
            totalHoursElement.textContent = `${volunteerData.totalHours || 0} hours`;

            // Display certificates
            const certificates = volunteerData.certificates || [];
            if (certificates.length > 0) {
                certificates.forEach((certificate, index) => {
                    const listItem = document.createElement("li");
                    listItem.innerHTML = `
                        <h3>Certificate ${index + 1}</h3>
                        <p><strong>Event:</strong> ${certificate.event}</p>
                        <p><strong>Date:</strong> ${certificate.date}</p>
                        <p><strong>Address:</strong> ${certificate.address}</p>
                        <a href="${certificate.downloadLink}" target="_blank" download>Download Certificate</a>
                    `;
                    certificatesListElement.appendChild(listItem);
                });
            } else {
                certificatesListElement.innerHTML = "<p>No certificates available.</p>";
            }
        } else {
            totalHoursElement.textContent = "No data available.";
            certificatesListElement.innerHTML = "<p>No certificates available.</p>";
        }
    </script>
</body>
</html>
