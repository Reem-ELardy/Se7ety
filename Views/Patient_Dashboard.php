<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="/Views/style_Patient.css">
</head>
<body>
    <div class="container">
        <header>
            <button onclick="location.href='LogoutController.php'" class="logout-button">Logout</button>
            <h1>Patient Dashboard</h1>
            <button onclick="location.href='HomeProfileController.php'" class="profile-button">Profile</button>
            <button onclick="showNotifications()" class="notification-button">Notifications</button>
        </header>

        <section class="dashboard">
            <!-- Event List Card -->
            <div class="card">
                <h2>Available Events</h2>
                <ul id="event-list">
                    <?php if (!empty($events)): ?>
                        <?php foreach ($events as $event): ?>
                            <li>
                                <h3><?= htmlspecialchars($event['name']) ?></h3>
                                <p>Date: <?= htmlspecialchars($event['date']) ?></p>
                                <p>Location: <?= htmlspecialchars($event['location']) ?></p>
                                <!-- Register/Unregister Button -->
                                <button 
                                    class="register-btn" 
                                    data-event-id="<?= htmlspecialchars($event['id']) ?>" 
                                    onclick="toggleEventRegistration(this)">
                                    <?= $event['isRegistered'] ? 'Unregister Event' : 'Register Event' ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No events available at the moment.</p>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Patient Needs List Card -->
            <div class="card">
                <h2>Your Needs</h2>
                <ul id="patient-need-list">
                    <?php if (!empty($patientNeeds)): ?>
                        <?php foreach ($patientNeeds as $index => $need): ?>
                            <li>
                                <h3>Need: <?= htmlspecialchars($need['name']) ?></h3>
                                <p>Status: <?= htmlspecialchars($need['status']) ?></p>
                                <button onclick="location.href='DeletePatientNeed.php?id=<?= htmlspecialchars($index) ?>'">Delete</button>
                                </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No needs created yet.</p>
                    <?php endif; ?>
                </ul>
                <div class="action-buttons">
                    <button onclick="location.href='/Views/Patient_Need.php'">Add New Need</button>
                </div>
            </div>
        </section>
    </div>

    <script>
        // Show notifications in a popup
        function showNotifications() {
            // Simulate fetching notifications from the backend
            fetch('/Controllers/GetNotificationsController.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const messages = data.notifications;
                        let messageList = messages.map(msg => `- ${msg}`).join('\n');
                        alert(`Notifications:\n\n${messageList}`);
                    } else {
                        alert('No notifications available at the moment.');
                    }
                })
                .catch(error => {
                    console.error('Error fetching notifications:', error);
                    alert('An error occurred while fetching notifications.');
                });
        }

        // Toggle event registration dynamically
        function toggleEventRegistration(button) {
            const eventId = button.getAttribute('data-event-id');
            const isRegistering = button.textContent.trim() === 'Register Event';

            // Simulate a backend request using fetch (replace with actual API call)
            fetch(`/Controllers/EventRegistrationController.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    eventId: eventId,
                    action: isRegistering ?  'register' : 'unregister'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle button text dynamically
                    button.textContent = isRegistering ? 'Unregister Event' : 'Register Event';
                    location.reload();
                } else {
                    alert('An error occurred: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Unable to process your request.');
            });
        }
    </script>
</body>
</html>
