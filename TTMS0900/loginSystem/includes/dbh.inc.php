<?php
$dBServername = "localhost";
$dBUsername = "root";
$dBPassword = "";
$dBName = "loginsystem";

//Luodaan yhteys tietokantaan
$conn = mysqli_connect($dBServername, $dBUsername, $dBPassword, $dBName);

// Tarkistetaan yhteys
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
