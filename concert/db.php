<?php
session_start(); // REQUIRED for $_SESSION to work

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'concert_db';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
