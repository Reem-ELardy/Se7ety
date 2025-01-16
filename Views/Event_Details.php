<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Details</title>
    <link rel="stylesheet" href="style_Home.css">
    <style>
        
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Event Details</h1>
        </header>

        <section class="event-details-container">
            <h2 id="event-name">Loading...</h2>
            <p><strong>Address:</strong> <span id="event-address">Loading...</span></p>
            <p><strong>Date:</strong> <span id="event-date">Loading...</span></p>
            <p><strong>Time:</strong> <span id="event-time">Loading...</span></p>
            <p><strong>Description:</strong> <span id="event-description">Loading...</span></p>
            <p><strong>Max Attendees:</strong> <span id="event-max-attendees">Loading...</span></p>
            <p><strong>Type:</strong> <span id="event-type">Loading...</span></p>
        </section>

        <button onclick="location.href='/../Controllers/VolunteerDashboard.php'" class="back-button">Back to Dashboard</button>
    </div>

    <script>
        // Sample event data for testing (replace with dynamic data from the controller)
        const eventDetails = <?= json_encode($data['eventDetails'] ?? null); ?>;

        // Populate event details
        if (eventDetails) {
            document.getElementById("event-name").textContent = eventDetails.name || "N/A";
            document.getElementById("event-address").textContent = eventDetails.address || "N/A";
            document.getElementById("event-date").textContent = eventDetails.date || "N/A";
            document.getElementById("event-time").textContent = eventDetails.time || "N/A";
            document.getElementById("event-description").textContent = eventDetails.description || "N/A";
            document.getElementById("event-max-attendees").textContent = eventDetails.maxAttendees || "N/A";
            document.getElementById("event-type").textContent = eventDetails.type || "N/A";
        } else {
            document.querySelector(".event-details-container").innerHTML = "<p>Event details not available.</p>";
        }
    </script>
</body>
</html>
