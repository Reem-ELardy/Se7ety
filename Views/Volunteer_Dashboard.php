<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard</title>
    <link rel="stylesheet" href="style_Home.css">
    <style>

    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Volunteer Dashboard</h1>
        </header>

        <section class="dashboard">
            <h2>Welcome, [Volunteer Name]!</h2>
            <p>Here’s a quick overview of your activities:</p>

            <h3>Upcoming Tasks</h3>
            <ul class="task-list" id="task-list">
                <li>
                    <h3>Event: Beach Cleanup</h3>
                    <p>Date: 2025-01-20</p>
                    <p>Address: maadi</p>
                    <button onclick="Event_Details.html">View Details</button>
                </li>
                <li>
                    <h3>Event: Food Distribution</h3>
                    <p>Date: 2025-01-25</p>
                    <p>Address: maadi</p>
                    <button onclick="Event_Details.html">View Details</button>
                </li>
            </ul>
            <button type="button" onclick="location.href='Certificate.php'" class="donate-button">Certificates</button>
        </section>


        <!-- <h2>Welcome, <?= htmlspecialchars($data['volunteerName']) ?>!</h2>
            <h3>Upcoming Tasks</h3>
            <ul class="task-list">
                <?php if (!empty($data['tasks'])): ?>
                    <?php foreach ($data['tasks'] as $task): ?>
                        <li>
                            <h3>Event: <?= htmlspecialchars($task['event']) ?></h3>
                            <p>Date: <?= htmlspecialchars($task['date']) ?></p>
                            <p>Address: <?= htmlspecialchars($task['address']) ?></p>
                            <button>View Details</button>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No upcoming tasks.</p>
                <?php endif; ?>
        </ul> -->

<!-- dah 3shan lma tygo t3mlo integrate -->

        <button onclick="location.href='/../Controllers/ProfilePage.php'" class="profile-button">Go to Profile</button>
        <button onclick="location.href='/../Controllers/GoingToHome.php'" class="back-button">Back to Home</button>
    </div>
</body>
</html>
