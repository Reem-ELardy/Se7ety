<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/Views/style_Home.css">
</head>
<body>
    <div class="container">
        <header>
        <button onclick="location.href='/Controllers/LogOutController.php'" class="logout-button">Logout</button>
        <h1>Admin Dashboard</h1>
        </header>

        <section class="dashboard-container">
            <h2>Event List</h2>
            <ul class="event-list">
                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $event): ?>
                        <li>
                            <h3><?= htmlspecialchars($event['name']) ?></h3>
                            <p><strong>Date:</strong> <?= htmlspecialchars($event['date']) ?></p>
                            <p><strong>Address:</strong> <?= htmlspecialchars($event['Address']) ?></p>
                            <div class="event-actions">
                                <button class="edit-button" onclick="location.href='EventAdminHomeToEditController.php?id=<?= htmlspecialchars($event['id']) ?>'"></button>
                                <button class="delete-button" onclick="deleteEvent(<?= htmlspecialchars($event['id']) ?>)"></button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No events available.</p>
                <?php endif; ?>
            </ul>
            <button onclick="location.href='EventCreationController.php'" class="add-event-button">Add New Event</button>
        </section>
    </div>

    <script>
        function deleteEvent(eventId) {
            if (confirm("Are you sure you want to delete this event?")) {
                
                fetch(`/../Controllers/DeleteEventController.php?id=${eventId}`, {
                    method: 'GET',
                })
                .then(response => {
                    if (response.ok) {
                        alert("Event deleted successfully.");
                        location.reload(); 
                    } else {
                        alert("Failed to delete the event.");
                    }
                })
                .catch(error => {
                    console.error("Error deleting event:", error);
                    alert("An error occurred.");
                });
            }
        }
    </script>
</body>
</html>
