<?php
require 'db.php'; // session_start is already called in db.php

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Handle booking
if (isset($_POST['book'])) {
    $event_id = $_POST['event_id'];
    $user_id = $_SESSION['user_id'];
    $quantity = max(1, intval($_POST['quantity']));

    $stmt = $conn->prepare("SELECT total_tickets, tickets_sold FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->bind_result($total, $sold);
    $stmt->fetch();
    $stmt->close();

    $available = $total - $sold;

    if ($available >= $quantity) {
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, event_id, quantity, payment_status, booking_date) VALUES (?, ?, ?, 'Pending', NOW())");
        $stmt->bind_param("iii", $user_id, $event_id, $quantity);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE events SET tickets_sold = tickets_sold + ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $event_id);
        $stmt->execute();

        echo "<p style='color:green;'>Booked $quantity ticket(s) successfully!</p>";
    } else {
        echo "<p style='color:red;'>Only $available ticket(s) left.</p>";
    }
}

// Handle payment and generate QR
if (isset($_POST['pay'])) {
    $booking_id = $_POST['booking_id'];
    $user_id = $_SESSION['user_id'];

    $qrContent = "Booking ID: $booking_id, User ID: $user_id";
    $qr_code_path = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($qrContent) . '&size=150x150';

    $stmt = $conn->prepare("UPDATE bookings SET payment_status = 'Paid', qr_code_path = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $qr_code_path, $booking_id, $user_id);
    if ($stmt->execute()) {
        echo "<p style='color:blue;'>Payment successful! QR Code generated below.</p>";
    } else {
        echo "<p style='color:red;'>Payment failed.</p>";
    }
}

// Handle removing unpaid booking
if (isset($_POST['remove_booking'])) {
    $booking_id = $_POST['booking_id'];
    $user_id = $_SESSION['user_id'];

    // First get booking details to reduce tickets_sold
    $stmt = $conn->prepare("SELECT event_id, quantity FROM bookings WHERE id = ? AND user_id = ? AND payment_status = 'Pending'");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($event_id, $qty);
    if ($stmt->fetch()) {
        $stmt->close();

        // Delete booking
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();

        // Update events table to reduce tickets_sold
        $stmt = $conn->prepare("UPDATE events SET tickets_sold = tickets_sold - ? WHERE id = ?");
        $stmt->bind_param("ii", $qty, $event_id);
        $stmt->execute();

        echo "<p style='color:orange;'>Booking removed.</p>";
    } else {
        echo "<p style='color:red;'>Cannot remove this booking.</p>";
    }
}

// Handle refund
if (isset($_POST['refund_booking'])) {
    $booking_id = $_POST['booking_id'];
    $user_id = $_SESSION['user_id'];

    // Get booking info
    $stmt = $conn->prepare("SELECT event_id, quantity FROM bookings WHERE id = ? AND user_id = ? AND payment_status = 'Paid'");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($event_id, $qty);
    if ($stmt->fetch()) {
        $stmt->close();

        // Delete booking
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();

        // Update event
        $stmt = $conn->prepare("UPDATE events SET tickets_sold = tickets_sold - ? WHERE id = ?");
        $stmt->bind_param("ii", $qty, $event_id);
        $stmt->execute();

        // Redirect to refund message page
        header("Location: refund.php");
        exit;
    } else {
        echo "<p style='color:red;'>Refund not possible.</p>";
    }
}

// Fetch events
$events = $conn->query("SELECT * FROM events ORDER BY event_date ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <style>
        body {
            background-image: url('assets/dua-lipa-1838653_1280.jpg');
            background-size: cover;
            font-family: Arial, sans-serif;
            color: #fff;
        }
        table {
            background-color: rgba(0, 0, 0, 0.7);
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 40px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #fff;
        }
        h2, h3 {
            text-shadow: 1px 1px 3px #000;
        }
        .btn {
            background: #0f0;
            border: none;
            padding: 8px 16px;
            cursor: pointer;
        }
        .btn:hover {
            background: #0c0;
        }
        .btn-danger {
            background: #f44336;
            color: white;
        }
        .btn-danger:hover {
            background: #d32f2f;
        }
        a {
            color: #ffd700;
        }
    </style>
</head>
<body>

<h2>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> (Customer)</h2>
<p><a href="logout.php">Logout</a></p>
<hr>

<h3>Available Concerts</h3>
<table>
    <tr>
        <th>Name</th>
        <th>Location</th>
        <th>Date</th>
        <th>Description</th>
        <th>Price (KES)</th>
        <th>Available</th>
        <th>Action</th>
    </tr>

    <?php while ($row = $events->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['location']) ?></td>
            <td><?= $row['event_date'] ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td>KES <?= number_format($row['ticket_price'], 2) ?></td>
            <td><?= $row['total_tickets'] - $row['tickets_sold'] ?></td>
            <td>
                <?php if ($row['tickets_sold'] < $row['total_tickets']): ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="event_id" value="<?= $row['id'] ?>">
                        Quantity: 
                        <input type="number" name="quantity" value="1" min="1" max="<?= $row['total_tickets'] - $row['tickets_sold'] ?>" required>
                        <button type="submit" name="book" class="btn">Book</button>
                    </form>
                <?php else: ?>
                    <span style="color:red;">Sold Out</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<hr>
<h3>My Bookings</h3>

<?php
$user_id = $_SESSION['user_id'];
$mybookings = $conn->prepare("
    SELECT b.id, b.quantity, b.booking_date, b.payment_status, e.name, e.location, e.event_date, b.qr_code_path
    FROM bookings b
    JOIN events e ON b.event_id = e.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$mybookings->bind_param("i", $user_id);
$mybookings->execute();
$result = $mybookings->get_result();

if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Concert</th>
            <th>Location</th>
            <th>Date</th>
            <th>Quantity</th>
            <th>Status</th>
            <th>Booked On</th>
            <th>QR Code</th>
            <th>Actions</th>
        </tr>
        <?php while ($b = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($b['name']) ?></td>
                <td><?= htmlspecialchars($b['location']) ?></td>
                <td><?= $b['event_date'] ?></td>
                <td><?= $b['quantity'] ?></td>
                <td><?= $b['payment_status'] ?></td>
                <td><?= $b['booking_date'] ?></td>
                <td>
                    <?php if ($b['payment_status'] === 'Paid'): ?>
                        <img src="<?= $b['qr_code_path'] ?>" alt="QR Code" width="100">
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($b['payment_status'] !== 'Paid'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                            <button type="submit" name="pay" class="btn">Pay</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                            <button type="submit" name="remove_booking" class="btn btn-danger">Remove</button>
                        </form>
                    <?php else: ?>
                        <form method="POST">
                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                            <button type="submit" name="refund_booking" class="btn btn-danger">Refund</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>You have no bookings yet.</p>
<?php endif; ?>

</body>
</html>
