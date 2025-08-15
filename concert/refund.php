<?php
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Refund Initiated</title>
    <style>
        body {
            background-color: #111;
            color: #fff;
            text-align: center;
            font-family: Arial, sans-serif;
            padding: 100px;
        }
    </style>
</head>
<body>
    <h2>Refund Initiated</h2>
    <p>Your refund details will be sent to your email.</p>
    <p>Please check your inbox and spam folder to process the refund.</p>
    <p><a href="user_dashboard.php" style="color: #0f0;">Back to Dashboard</a></p>
</body>
</html>
