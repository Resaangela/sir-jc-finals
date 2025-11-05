<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = dbConnect();

    $user_id = $_SESSION['user_id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date = $_POST['schedule_date'] ?? null;

    if ($user_id && $title && $date) {
        try {
            $stmt = $pdo->prepare("INSERT INTO schedules (user_id, title, description, schedule_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user_id, $title, $description, $date]);
            header("Location: schedule_tracker.php?added=1");
            exit;
        } catch (PDOException $e) {
            echo "Database error: " . htmlspecialchars($e->getMessage());
        }
    } else {
        echo "Please fill in all required fields.";
    }
} else {
    header("Location: schedule_tracker.php");
    exit;
}
?>
