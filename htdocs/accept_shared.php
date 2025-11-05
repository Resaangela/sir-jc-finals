<?php
require 'config.php';
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$user = currentUser($pdo);
$pdo = dbConnect();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $sharedId = (int)$_POST['id'];

    $stmt = $pdo->prepare("SELECT * FROM shared_tasks WHERE id = ? AND collaborator_id = ?");
    $stmt->execute([$sharedId, $user['id']]);
    $shared = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($shared) {
        $pdo->prepare("UPDATE shared_tasks SET accepted = 1 WHERE id = ?")->execute([$sharedId]);
    }
}

header("Location: shared_tasks.php");
exit;
?>
