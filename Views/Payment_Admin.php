<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Admin</title>
        <link rel="stylesheet" href="/Views/style_Home.css">
    </head>
    <body>
        <div class="container">
            <header>
                <a href="/Controllers/LogOutController.php" class="back-button">logout</a>
            </header>

            <section class="payment-card">
                <div class="card">
                    <h2>Payment Summary</h2>
                    <p><strong>Total Donations Done:</strong> <span id="total-donations-done">0</span></p>
                    <p><strong>Total Donations Pending:</strong> <span id="total-donations-pending">0</span></p>
                </div>
            </section>
        </div>

        <script>
            const paymentData = <?= json_encode($data['paymentData'] ?? null); ?>;
            
            // Display data on the page
            document.getElementById("total-donations-done").innerHTML = paymentData['totalDone'] || "N/A";
            document.getElementById("total-donations-pending").innerHTML = paymentData['totalPending'] || "N/A";
        </script>
    </body>
</html>
