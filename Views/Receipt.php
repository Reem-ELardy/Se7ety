<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link rel="stylesheet" href="/Views/style_Home.css">
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
            const transactionData = receiptData[0]; // Access the first item in the array

            receiptDetailsDiv.innerHTML = `
                <div class="receipt-details">
                    <strong>Name: </strong>${receiptData['Donor'] || 'N/A'}<br>
                    <strong>Transaction ID: </strong> ${transactionData['Transaction_ID'] || 'N/A'}<br>
                    <strong>Payment Method: </strong> ${transactionData['Payment_Method'] || 'N/A'}<br>
                    <strong>Date:</strong> ${receiptData['Date'] || 'N/A'}<br>
                </div>
            `;

            if(receiptData['Medical Items'] && receiptData['Medical Items']){
                let details= `
                    <hr>
                    <strong>Medical Items:</strong><br>
                    <ul>
                `;
                const medicalItems = receiptData['Medical Items'];

                medicalItems.forEach((item, index) => {
                    details += `<li class="receipt-item">
                        <span class="item-name">${item['Name']}</span>
                        <span class="dots">---------------</span>
                        <span class="item-quantity">${item['Quantity']}</span>
                    </li>`;
                });
                details += `
                    <ul>
                    <br>
                `;

                receiptDetailsDiv.innerHTML += details;
                receiptDetailsDiv.innerHTML +=`<strong>Medical Tax: </strong>${(receiptData['Medical Tax Value'])}<br>`;
            }

            if(receiptData['Donation Amount'] && receiptData['Money Tax Value']){
                receiptDetailsDiv.innerHTML += `
                    <hr>
                    <strong>Amount of Money: </strong>${(receiptData['Donation Amount'])}<br>
                    <strong>Money Tax: </strong>${(receiptData['Money Tax Value'])}<br>
                `;
            }

            receiptDetailsDiv.innerHTML += `
                <hr>
                <strong>Total Amount:</strong>${(receiptData['Total Donation'])}<br>
            `;
            receiptDetailsDiv.innerHTML += details;

        } else {
            receiptDetailsDiv.innerHTML = `<p>No receipt details available.</p>`;
        }
    </script>
</body>
</html>
