<?php
require 'config.php';
if (!isLoggedIn()) exit;
$pdo = dbConnect();
$new = $_POST['dark'] == 1 ? 1 : 0;
$pdo->prepare("UPDATE users SET dark_mode = ? WHERE id = ?")->execute([$new, $_SESSION['user_id']]);
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;
?>
