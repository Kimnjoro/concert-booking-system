<?php
require 'db.php';

// Get upcoming concerts
$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT * FROM events WHERE event_date >= ? ORDER BY event_date ASC");
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Concert Booking System</title>
</head>
<body>
    <h1>🎶 Welcome to Concert Booker</h1>

    <?php if (isset($_SESSION['user_id'])): ?>
        <p>Welcome back, <?= htmlspecialchars($_SESSION['name']) ?> |
            <a href="<?= $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php' ?>">Dashboard</a> |
            <a href="logout.php">Logout</a>
        </p>
    <?php else: ?>
        <p>
            <a href="login.php">Login</a> |
            <a href="register.php">Register</a>
        </p>
    <?php endif; ?>

    <hr>

    <h2>Upcoming Concerts</h2>

    <?php if ($result->num_rows > 0): ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>Name</th>
                <th>Location</th>
                <th>Date</th>
                <th>Description</th>
                <th>Price</th>
                <th>Tickets Left</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><?= $row['event_date'] ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>$<?= number_format($row['ticket_price'], 2) ?></td>
                    <td><?= $row['total_tickets'] - $row['tickets_sold'] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No upcoming concerts at the moment.</p>
    <?php endif; ?>
</body>
</html>
