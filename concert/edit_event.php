<?php
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid event ID.";
    exit;
}

$event_id = intval($_GET['id']);

// Handle update
if (isset($_POST['update_event'])) {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $event_date = $_POST['event_date'];
    $description = $_POST['description'];
    $price = floatval($_POST['price']);
    $total_tickets = intval($_POST['total_tickets']);

    $stmt = $conn->prepare("UPDATE events SET name=?, location=?, event_date=?, description=?, ticket_price=?, total_tickets=? WHERE id=?");
    $stmt->bind_param("ssssdii", $name, $location, $event_date, $description, $price, $total_tickets, $event_id);
    $stmt->execute();
    $success = "Event updated successfully.";
}

// Fetch event
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    echo "Event not found.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
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
            max-width: 700px;
            margin: 60px auto;
            background: rgba(0, 0, 0, 0.85);
            border-radius: 10px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #ffeb3b;
        }

        .success {
            background-color: #28a745;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            border-radius: 5px;
            color: #fff;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            background: #333;
            color: #fff;
            border: 1px solid #555;
            border-radius: 5px;
        }

        button {
            padding: 10px 20px;
            background-color: #ffc107;
            color: #000;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #e0a800;
        }

        a.back {
            display: inline-block;
            margin-top: 15px;
            color: #90caf9;
            text-decoration: none;
        }

        a.back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Event</h2>

    <?php if (isset($success)): ?>
        <div class="success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="name" value="<?= htmlspecialchars($event['name']) ?>" required>
        <input type="text" name="location" value="<?= htmlspecialchars($event['location']) ?>" required>
        <input type="date" name="event_date" value="<?= $event['event_date'] ?>" required>
        <textarea name="description" rows="4" required><?= htmlspecialchars($event['description']) ?></textarea>
        <input type="number" name="price" step="0.01" min="0" value="<?= $event['ticket_price'] ?>" required>
        <input type="number" name="total_tickets" min="1" value="<?= $event['total_tickets'] ?>" required>

        <button type="submit" name="update_event">Update Event</button>
    </form>

    <a href="admin_dashboard.php" class="back">← Back to Dashboard</a>
</div>
</body>
</html>
