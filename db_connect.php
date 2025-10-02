<?php
$host = "localhost"; // Ganti dengan host database Anda
$user = "root"; // Ganti dengan username database Anda
$pass = ""; // Ganti dengan password database Anda
$db   = "memorymatch_db";     // Ganti dengan nama database Anda

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>