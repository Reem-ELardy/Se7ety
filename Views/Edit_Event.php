<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link rel="stylesheet" href="style_Home.css">

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
                    <select name="city" id="event-address-city" required>
                        <option value="City1" <?= isset($event['city']) && $event['city'] == 'City1' ? 'selected' : '' ?>>City1</option>
                        <option value="City2" <?= isset($event['city']) && $event['city'] == 'City2' ? 'selected' : '' ?>>City2</option>
                        <option value="City3" <?= isset($event['city']) && $event['city'] == 'City3' ? 'selected' : '' ?>>City3</option>
                    </select>
                    <select name="region" id="event-address-region" required>
                        <option value="Region1" <?= isset($event['region']) && $event['region'] == 'Region1' ? 'selected' : '' ?>>Region1</option>
                        <option value="Region2" <?= isset($event['region']) && $event['region'] == 'Region2' ? 'selected' : '' ?>>Region2</option>
                        <option value="Region3" <?= isset($event['region']) && $event['region'] == 'Region3' ? 'selected' : '' ?>>Region3</option>
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
                        <option value="Donation Collect" <?= isset($event['type']) && $event['type'] == 'Donation Collect' ? 'selected' : '' ?>>Donation Collect</option>
                        <option value="Medical Tour" <?= isset($event['type']) && $event['type'] == 'Medical Tour' ? 'selected' : '' ?>>Medical Tour</option>
                        <option value="Other" <?= isset($event['type']) && $event['type'] == 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="submit-button">Update Event</button>
            </form>
            <button onclick="location.href='/../Controllers/AdminDashboard.php'" class="back-button">Back to Dashboard</button>
        </section>
    </div>
</body>
</html>
