<?php
$servername = "mysql-vapeur-vapeur.i.aivencloud.com";
$port = 17770;
$username = "avnadmin";
$password = "AVNS_-XoE6EKsHchyzf7ZeR-";
$dbname = "defaultdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>