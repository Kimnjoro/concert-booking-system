<?php
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Handle add event
if (isset($_POST['add_event'])) {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $date = $_POST['event_date'];
    $desc = $_POST['description'];
    $price = floatval($_POST['price']);
    $tickets = intval($_POST['tickets']);

    $stmt = $conn->prepare("INSERT INTO events (name, location, event_date, description, ticket_price, total_tickets, tickets_sold) VALUES (?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("ssssdi", $name, $location, $date, $desc, $price, $tickets);
    $stmt->execute();
    $success = "Event added successfully.";
}

// Handle delete event
if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $success = "Event deleted.";
}

// Fetch all events
$events = $conn->query("SELECT * FROM events ORDER BY event_date ASC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard – Event Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('assets/confetti-2571539_1280.jpg');
            background-size: cover;
            background-position: center;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            color: #fff;
        }

        .container {
            padding: 30px;
            background: rgba(0, 0, 0, 0.75);
            min-height: 100vh;
        }

        h2, h3 {
            text-align: center;
            margin-bottom: 20px;
            color: #ffeb3b;
        }

        .success {
            background: #28a745;
            padding: 10px;
            border-radius: 5px;
            color: #fff;
            margin: 20px auto;
            text-align: center;
            max-width: 600px;
        }

        .logout {
            text-align: right;
            margin-bottom: 20px;
        }

        .logout a {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
        }

        .form-section {
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 10px;
            max-width: 600px;
            margin: 0 auto 40px;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            background: #222;
            color: #fff;
            border: 1px solid #555;
            border-radius: 5px;
        }

        button {
            background-color: #ffc107;
            color: #000;
            padding: 10px 20px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
        }

        button:hover {
            background-color: #e0a800;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: rgba(0, 0, 0, 0.85);
        }

        th, td {
            padding: 12px;
            border: 1px solid #444;
            text-align: center;
        }

        th {
            background-color: #111;
            color: #fdd835;
        }

        .btn {
            padding: 8px 12px;
            background-color: #03a9f4;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn:hover {
            background-color: #0288d1;
        }

        .btn-danger {
            background-color: #e53935;
        }

        .btn-danger:hover {
            background-color: #c62828;
        }
    </style>
</head>
<body>
<div class="container">

    <div class="logout">
        Logged in as <?= htmlspecialchars($_SESSION['name']) ?> |
        <a href="logout.php" class="btn">Logout</a>
    </div>

    <h2>Admin Dashboard</h2>

    <?php if (isset($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <h3>Add New Event</h3>
    <div class="form-section">
        <form method="POST">
            <input type="text" name="name" placeholder="Event Name" required>
            <input type="text" name="location" placeholder="Location" required>
            <input type="date" name="event_date" required>
            <textarea name="description" placeholder="Description" rows="3" required></textarea>
            <input type="number" name="price" placeholder="Ticket Price (KES)" min="0" step="0.01" required>
            <input type="number" name="tickets" placeholder="Total Tickets" min="1" required>
            <button type="submit" name="add_event">Add Event</button>
        </form>
    </div>

    <h3>All Events</h3>
    <table>
        <thead>
            <tr>
                <th>Event</th>
                <th>Location</th>
                <th>Date</th>
                <th>Description</th>
                <th>Price (KES)</th>
                <th>Total</th>
                <th>Sold</th>
                <th>Available</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $events->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><?= $row['event_date'] ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= number_format($row['ticket_price'], 2) ?></td>
                    <td><?= $row['total_tickets'] ?></td>
                    <td><?= $row['tickets_sold'] ?></td>
                    <td><?= $row['total_tickets'] - $row['tickets_sold'] ?></td>
                    <td>
                        <a href="edit_event.php?id=<?= $row['id'] ?>" class="btn">Edit</a>
                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this event?')" class="btn btn-danger">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>
</body>
</html>
