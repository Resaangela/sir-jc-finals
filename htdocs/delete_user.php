<?php
require_once 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}
$user = currentUser();
if ($user['role'] !== 'admin') {
    echo "<h2 style='text-align:center;color:red;margin-top:50px;'>Access Denied</h2>";
    exit;
}

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: admin_panel.php");
exit;
?>
