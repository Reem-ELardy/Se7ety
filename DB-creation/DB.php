<?php
require_once "DB-Connection.php";

$dbname = "Se7ety";

run_queries(
    queries: [
        "DROP DATABASE IF EXISTS $dbname",

        "CREATE DATABASE IF NOT EXISTS $dbname",

        "CREATE TABLE IF NOT EXISTS $dbname.Address (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            Name VARCHAR(100),
            ParentAddressID INT,
            IsDeleted tinyint(1) NOT NULL DEFAULT 0,
            FOREIGN KEY (ParentAddressID) REFERENCES Address(ID)
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.Person (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            Name VARCHAR(50),
            Age INT,
            Password VARCHAR(50),
            Email VARCHAR(50),
            AddressID INT,
            IsDeleted tinyint(1) NOT NULL DEFAULT 0,
            FOREIGN KEY (AddressID) REFERENCES Address(ID)
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.Donor (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            PersonID INT,
            FOREIGN KEY (PersonID) REFERENCES Person(ID)
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.Patient (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            PersonID INT,
            FOREIGN KEY (PersonID) REFERENCES Person(ID)
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.Volunteer (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            PersonID INT,
            Job ENUM('Doctor', 'Nurse', 'Other'),
            VolunteerHours INT,
            Available BOOLEAN,
            Gender ENUM('Male', 'Female'),
            FOREIGN KEY (PersonID) REFERENCES Person(ID)
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.Skills (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            Name VARCHAR(50),
            IsDeleted tinyint(1) NOT NULL DEFAULT 0
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.VolunteerSkills (
            VolunteerID INT,
            SkillID INT,
            IsDeleted tinyint(1) NOT NULL DEFAULT 0,
            FOREIGN KEY (VolunteerID) REFERENCES Volunteer(ID),
            FOREIGN KEY (SkillID) REFERENCES Skills(ID)
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.Communication (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            PersonID INT,
            Type ENUM('SMS', 'E-Mail'),
            Message TEXT,
            IsDeleted tinyint(1) NOT NULL DEFAULT 0,
            FOREIGN KEY (PersonID) REFERENCES Person(ID)
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.Donate (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            DonorID INT,
            Date DATE,
            Time TIME,
            IsDeleted tinyint(1) NOT NULL DEFAULT 0,
            FOREIGN KEY (DonorID) REFERENCES Donor(ID) ON DELETE CASCADE
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.Donation (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            DonateID INT,
            Type ENUM('Medical', 'Cash'),
            CashAmount DECIMAL(10, 2),
            IsDeleted tinyint(1) NOT NULL DEFAULT 0,
            FOREIGN KEY (DonateID) REFERENCES Donate(ID)
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.Medical (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            Name VARCHAR(50),
            Type ENUM('Tool', 'Medicine'),
            ExpirationDate DATE,
            Quantity INT
            IsDeleted tinyint(1) NOT NULL DEFAULT 0,
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.PatientNeed (
            MedicalID INT,
            PatientID INT,
            Status ENUM('Waiting', 'Accepted', 'Done') ,
            IsDeleted tinyint(1) NOT NULL DEFAULT 0,
            FOREIGN KEY (MedicalID) REFERENCES Medical(ID),
            FOREIGN KEY (PatientID) REFERENCES Patient(ID)
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.DonationMedical (
            DonationID INT,
            MedicalID INT,
            Quantity INT,
            IsDeleted tinyint(1) NOT NULL DEFAULT 0,
            FOREIGN KEY (DonationID) REFERENCES Donation(ID),
            FOREIGN KEY (MedicalID) REFERENCES Medical(ID)
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.Receipt (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            DonateID INT,
            IsDeleted tinyint(1) NOT NULL DEFAULT 0,
            FOREIGN KEY (DonateID) REFERENCES Donate(ID)
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.Event (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            Name VARCHAR(50),
            Date DATE,
            Type ENUM('Donation-Collect', 'Medical-Tour', 'Other'),
            TotalNoPatients INT,
            TotalNoVolunteers INT,
            LocationID INT,
            IsDeleted tinyint(1) NOT NULL DEFAULT 0,
            FOREIGN KEY (LocationID) REFERENCES Address(ID)
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.EventParticipation (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            VolunteerID INT,
            EventID INT,
            Role VARCHAR(50),
            ParticipantHours INT,
            IsDeleted tinyint(1) NOT NULL DEFAULT 0,
            FOREIGN KEY (VolunteerID) REFERENCES Volunteer(ID),
            FOREIGN KEY (EventID) REFERENCES Event(ID)
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.Certificate (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            VolunteerID INT,
            EventID INT,
            IsDeleted tinyint(1) NOT NULL DEFAULT 0,
            FOREIGN KEY (VolunteerID) REFERENCES Volunteer(ID),
            FOREIGN KEY (EventID) REFERENCES Event(ID)
        )",

        "CREATE TABLE IF NOT EXISTS $dbname.Ticket (
            ID INT PRIMARY KEY AUTO_INCREMENT,
            EventID INT,
            PatientID INT,
            IsDeleted tinyint(1) NOT NULL DEFAULT 0,
            FOREIGN KEY (EventID) REFERENCES Event(ID),
            FOREIGN KEY (PatientID) REFERENCES Patient(ID)
        )"
    ],
    echo:true
);


?>
