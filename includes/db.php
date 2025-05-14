<?php

$servername = "localhost";
$dbname = "eventmanager";
$username = "root";
$db_password = "";

$conn = new mysqli($servername, $username, $db_password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
