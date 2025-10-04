<?php
$servername = "localhost";
$username = "root";   // change if different
$password = "";       // your phpMyAdmin password if any
$dbname = "visitor_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
