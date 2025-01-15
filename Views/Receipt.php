<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link rel="stylesheet" href="style_Home.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Receipt</h1>
        </header>

        <section class="receipt-container">
            <h2>Payment Receipt</h2>
            <div id="receipt-details">
                <!-- Receipt details will be dynamically inserted here ready for integration -->
            </div>
            <button onclick="location.href='/../Controllers/GoingToHome.php'" class="back-button">Back to Home</button>
        </section>
    </div>

    <script>
        // Sample receipt data for testing (replace with data from the controller)
        const receiptData = <?= json_encode($data['receiptDetails'] ?? null); ?>;

        const receiptDetailsDiv = document.getElementById("receipt-details");

        if (receiptData) {
            receiptDetailsDiv.innerHTML = `
                <div class="receipt-details">
                    <strong>Transaction ID:</strong> ${receiptData.transactionId || 'N/A'}<br>
                    <strong>Payment Method:</strong> ${receiptData.paymentMethod || 'N/A'}<br>
                    <strong>Amount Paid:</strong> ${receiptData.amount || 'N/A'}<br>
                    <strong>Date:</strong> ${receiptData.date || 'N/A'}<br>
                    <strong>Status:</strong> ${receiptData.status || 'N/A'}<br>
                </div>
            `;
        } else {
            receiptDetailsDiv.innerHTML = `<p>No receipt details available.</p>`;
        }
    </script>
</body>
</html>
