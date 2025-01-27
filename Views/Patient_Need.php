<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Patient Need</title>
    <link rel="stylesheet" href="/Views/style_Patient.css">
    <style>
        

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 20px;
        }

        button {
            padding: 10px 15px;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .add-button {
            background-color: #007bff;
        }

        .add-button:hover {
            background-color: #0056b3;
        }

        .save-button {
            background-color: #28a745;
        }

        .save-button:hover {
            background-color: #218838;
        }

        /* Back Button Styling */
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            background-color: #6c757d;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <!-- Back Button -->
    <a href="/Controllers/PatinetHomeController.php" class="back-button">Back to Dashboard</a>

    <div class="container">
        <h1>Create Need</h1>
        <form action="/Controllers/PatientNeedController.php" method="POST" id="create-need-form">
            <div id="medical-names">
                <div class="form-group">
                    <label for="medical-name-1">Medical Name 1:</label>
                    <input type="text" name="medicalNames[]" id="medical-name-1" placeholder="Enter medical name" required>
                </div>
            </div>

            <div class="button-group">
                <button type="button" class="add-button" id="add-medical-name">Add Another</button>
                <button type="submit" class="save-button">Save Need</button>
            </div>
        </form>
    </div>

    <script>
        // Dynamically add medical name inputs
        let medicalNameCount = 1;

        document.getElementById('add-medical-name').addEventListener('click', () => {
            medicalNameCount++;

            // Create a new form group for the additional medical name
            const newMedicalNameGroup = document.createElement('div');
            newMedicalNameGroup.classList.add('form-group');
            newMedicalNameGroup.innerHTML = `
                <label for="medical-name-${medicalNameCount}">Medical Name ${medicalNameCount}:</label>
                <input type="text" name="medicalNames[]" id="medical-name-${medicalNameCount}" placeholder="Enter medical name" required>
            `;

            // Append the new group to the medical-names container
            document.getElementById('medical-names').appendChild(newMedicalNameGroup);
        });
    </script>
</body>
</html>
