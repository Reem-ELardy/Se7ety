<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link rel="stylesheet" href="/Views/style_Home.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Payment</h1>
        </header>

        <section class="payment-methods">
            <h3>Select Payment Method</h3>
            <form id="payment-form" action="/Controllers/Payment_PageController.php" method="POST">
                <div class="form-group">
                    <label>
                        <input type="radio" name="payment-method" value="cash" id="cash-method">
                        Cash
                    </label>
                    <label>
                        <input type="radio" name="payment-method" value="card" id="credit-method">
                        Credit Card
                    </label>
                    <label>
                        <input type="radio" name="payment-method" value="ewallet" id="ewallet-method">
                        E-Wallet
                    </label>
                </div>

                <!-- Cash Payment Section -->
                <div id="cash-section" style="display: none;">
                    <p>You have selected to pay by cash.</p>
                    <hr>
                </div>

                <!-- Credit Card Payment Section -->
                <div id="credit-section" style="display: none;">
                    <div class="form-group">
                        <label for="card-number">Card Number:</label>
                        <input type="text" id="card-number" name="card-number" placeholder="Enter your card number">
                    </div>
                    <div class="form-group">
                        <label for="expiry-date">Expiry Date:</label>
                        <input type="month" id="expiry-date" name="expiry-date">
                    </div>
                    <div class="form-group">
                        <label for="cvv">CVV:</label>
                        <input type="text" id="cvv" name="cvv" placeholder="Enter CVV">
                    </div>
                    <hr>
                </div>

                <!-- E-Wallet Payment Section -->
                <div id="ewallet-section" style="display: none;">
                    <div class="form-group">
                        <label for="ewallet-number">E-Wallet Number:</label>
                        <input type="text" id="ewallet-number" name="ewallet-number" placeholder="Enter your e-wallet number">
                    </div>
                    <hr>
                </div>

                <!-- Tax and Total Calculation Section -->
                <div id="payment-summary" style="display: none;">
                    <h4>Payment Summary</h4>
                    <p id="tax-summary">Tax: $0.00</p>
                    <p id="total-summary">Total: $0.00</p>
                </div>

                <button type="submit" class="submit-button">Submit Payment</button>
            </form>
        </section>

        <button onclick="location.href='/../Controllers/GoingToHome.php'" class="back-button">Back to Home</button>
    </div>

    <script>
        // Handle the display of payment sections based on the selected method
        const cashMethod = document.getElementById('cash-method');
        const creditMethod = document.getElementById('credit-method');
        const ewalletMethod = document.getElementById('ewallet-method');

        const cashSection = document.getElementById('cash-section');
        const creditSection = document.getElementById('credit-section');
        const ewalletSection = document.getElementById('ewallet-section');

        const paymentSummary = document.getElementById('payment-summary');
        const taxSummary = document.getElementById('tax-summary');
        const totalSummary = document.getElementById('total-summary');

        const paymentSections = [cashSection, creditSection, ewalletSection];

        function updatePaymentSummary(paymentMethod) {
            // Send the payment method to the server via AJAX
            fetch('/Controllers/CalculationController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ paymentMethod: paymentMethod })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Server response:', data);
                taxSummary.textContent = `Tax: $${data.tax.toFixed(0)}`;
                totalSummary.textContent = `Total: $${data.total.toFixed(0)}`;
                paymentSummary.style.display = 'block';
            })
            .catch(error => console.error('Error:', error));

        }

        function hideAllSections() {
            paymentSections.forEach(section => section.style.display = 'none');
        }

        cashMethod.addEventListener('change', () => {
            hideAllSections();
            updatePaymentSummary('cash');
            cashSection.style.display = 'block';
        });

        creditMethod.addEventListener('change', () => {
            hideAllSections();
            updatePaymentSummary('card');
            creditSection.style.display = 'block';
        });

        ewalletMethod.addEventListener('change', () => {
            hideAllSections();
            updatePaymentSummary('ewallet');
            ewalletSection.style.display = 'block';
        });
    </script>
</body>
</html>
