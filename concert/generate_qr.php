<?php
// Include the PHP QR Code library
require_once 'qrlib.php'; // Adjust this path to where you've saved the PHP QR Code library

// Check if the form is submitted with text to generate the QR code
if (isset($_POST['generate_qr'])) {
    $data = $_POST['data']; // The data (text) to encode in the QR code
    $user_id = $_POST['user_id']; // To use it as part of the filename (e.g., user-specific QR code)
    
    // Set the directory where the QR code images will be saved
    $qr_dir = 'phpqrcode/';
    
    // Make sure the directory exists
    if (!is_dir($qr_dir)) {
        mkdir($qr_dir, 0777, true);
    }

    // Generate a unique filename for the QR code
    $filename = $qr_dir . $user_id . '.png'; // e.g., phpqrcode/1.png (based on user ID)

    // Generate the QR code and save it as a PNG image
    QRcode::png($data, $filename);

    // Output the image path
    echo "<p>QR Code generated successfully!</p>";
    echo "<p>QR Code saved as: <a href='" . $filename . "'>View QR Code</a></p>";
} else {
    echo "<p>No data to generate QR Code.</p>";
}
?>

<!-- Simple form to accept text and user ID -->
<form method="post" action="">
    <label for="data">Enter the data to encode in QR Code:</label><br>
    <input type="text" name="data" id="data" required><br><br>

    <label for="user_id">Enter User ID (for filename):</label><br>
    <input type="text" name="user_id" id="user_id" required><br><br>

    <button type="submit" name="generate_qr">Generate QR Code</button>
</form>
