<?php
import dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Get environment variables
$servername = $_ENV['servername'];
$port = $_ENV['port'];
$username = $_ENV['username'];
$password = $_ENV['password'];
$dbname = $_ENV['dbname'];

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>