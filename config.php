<?php

$host = "localhost";

$user = "root"; // default for XAMPP

$pass = ""; // default is empty

$dbname = "foodsave_db";

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection

if ($conn->connect_error) {

die("Connection failed: " . $conn->connect_error);

}

?>