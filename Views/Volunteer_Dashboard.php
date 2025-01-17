
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard</title>
    <link rel="stylesheet" href="/Views/style_Home.css">
    <style>

    </style>
</head>
<body>
    <!-- <div class="container">
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
                    <button onclick="location.href='/../Views/Event_Details.html'">View Details</button>
                </li>
            </ul>
            <button type="button" onclick="Certificates.php" class="donate-button">Certificates</button>
        </section> -->
        <section class="dashboard">
            <h2>Welcome, <?= htmlspecialchars($data['volunteerName']) ?>!</h2>
            <h3>Upcoming Tasks</h3>
            <ul class="task-list">
                <?php if (!empty($data['tasks'])): ?>
                    <?php foreach ($data['tasks'] as $task): ?>
                     
                        <li>
                            <h3>Event: <?= htmlspecialchars($task['Name']) ?></h3>
                            <p>Date: <?= htmlspecialchars($task['Date-Time']) ?></p>
                            <p>Role: <?= htmlspecialchars($task['Role']) ?></p>
                            <form method="POST" action="\..\Controllers\EventDetailsController.php">
                               <input type="hidden" name="id" value= <?= htmlspecialchars($task['id']) ?><"> <!-- Donation ID -->
                               <button type="submit">View Details</button>
                               </form>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No upcoming tasks.</p>
                <?php endif; ?>
            </ul>
            <button type="button" onclick="location.href='/../Controllers/CertificateDashboardCnotroller.php'" class="donate-button">Certificates</button>
        </section>

<!-- dah 3shan lma tygo t3mlo integrate -->

        <button onclick="location.href='/../Controllers/VoluntertProfile.php'" class="profile-button">Go to Profile</button>
        <button onclick="location.href='/../Controllers/LogOutController.php'" class="back-button">logout</button>
    </div>
    <script>
        
        function redirectToDetails(id) {
    // Redirect to another screen with the id as a query parameter
         window.location.href = `/../Controllers/EventDetailsController.php?id=${id}`;
        }
    </script>
</body>


</html>




