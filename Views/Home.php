<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="/Views/style_Home.css">
</head>
<body>
    <div class="container">
        <header>
            <button onclick="location.href='LogoutController.php'" class="logout-button">Logout</button>
            <h1>Welcome to Se7ety</h1>
        </header>
        <section class="donations">
            <h2>Previous Donations</h2>
            <ul id="donations-list">
                
            </ul>
        </section>
        <button class="donate-button" onclick="location.href='/Views/Make_Donation.php'">Make a Donation</button>
    </div>

    
    <a href="HomeProfileController.php" class="corner-button">Profile Page</a>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const donations = <?= json_encode($data['donationData']); ?>;

            const donationsList = document.getElementById("donations-list");
            if(donations == null){
                donationsList.innerHTML = "No Previous Donations"; 
            }else{
                donationsList.innerHTML = ""; 
            }


            donations.forEach((donation,index) => {
                const listItem = document.createElement("li");
                const anchor = document.createElement("a");
                anchor.href = `/../Controllers/Donation_DetailsController.php?id=${donation.id}`;              
                if(donation['type'] == 'Medical'){
                    anchor.textContent = `Donation #${index + 1}: Donate with ${donation['Quantity']} Medicals`;
                }else if(donation['type'] == 'Money'){
                    anchor.textContent = `Donation #${index + 1}: Donate with ${donation['cashamount']}$`;
                }else{
                    anchor.textContent = `Donation #${index + 1}: Donate with ${donation['Quantity']} Medicals & ${donation['cashamount']}$`;
                }
                listItem.appendChild(anchor);
                donationsList.appendChild(listItem);
            });
        });
    </script>
</body>
</html>
