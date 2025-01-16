<?php
require_once __DIR__ . '/IDatabase.php';
require_once "DB-Connection.php";

$dbname = "Se7ety";

// Populate the Skills table
run_queries(
    queries: [
        // Insert skills into the Skills table
        "INSERT INTO Skills (Name) VALUES 
        ('First Aid'),
        ('Public Speaking'),
        ('Leadership'),
        ('Teamwork'),
        ('Organizing Events'),
        ('Technical Support'),
        ('Conflict Resolution'),
        ('Teaching Skills'),
        ('Problem Solving'),
        ('Language Translation');"
    ],
    echo: true
);
