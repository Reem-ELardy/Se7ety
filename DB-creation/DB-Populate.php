<?php
require_once "DB-Connection.php";

$dbname = "Se7ety";

// Bunch of CRUDing without the U to populate our lovely database
run_queries(
    queries: [
            //Insert cities into the Address table
            "INSERT INTO Address (Name, ParentAddressID) VALUES 
            ('Cairo', NULL),
            ('Giza', NULL),
            ('Alexandria', NULL),
            ('Luxor', NULL),
            ('Aswan', NULL);",
            
            // Insert districts connected to Cairo
            "INSERT INTO Address (Name, ParentAddressID) VALUES 
            ('Maadi', 1),
            ('Nasr City', 1),
            ('Heliopolis', 1),
            ('Shubra', 1),
            ('Zamalek', 1);",
            
            //Insert districts connected to Giza
            "INSERT INTO Address (Name, ParentAddressID) VALUES 
            ('Dokki', 2),
            ('Mohandessin', 2),
            ('6th of October City', 2),
            ('Sheikh Zayed', 2),
            ('Faisal', 2);",
            
            //Insert districts connected to Alexandria
            "INSERT INTO Address (Name, ParentAddressID) VALUES 
            ('Sidi Gaber', 3),
            ('Smouha', 3),
            ('Mandara', 3),
            ('El Raml Station', 3),
            ('Montazah', 3);",
            
            //Insert districts connected to Luxor
            "INSERT INTO Address (Name, ParentAddressID) VALUES 
            ('Karnak', 4),
            ('East Bank', 4),
            ('West Bank', 4),
            ('Armant', 4),
            ('Esna', 4);",
            
            //Insert districts connected to Aswan
            "INSERT INTO Address (Name, ParentAddressID) VALUES 
            ('Aswan City', 5),
            ('Kom Ombo', 5),
            ('Edfu', 5),
            ('Abu Simbel', 5),
            ('Darb El-Arbaeen', 5);"
            
    ],
    echo: true
);
