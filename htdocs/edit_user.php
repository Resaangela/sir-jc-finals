
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
if (!$id) {
    header("Location: admin_panel.php");
    exit;
}

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$editUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$editUser) {
    die("User not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, role = ? WHERE id = ?");
    $stmt->execute([$name, $email, $role, $id]);

    header("Location: admin_panel.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit User | PlanMyStudy</title>
<link rel="stylesheet" href="style.css">
</head>
<body class="centered">
 

  <div class="card small">
    <a href="admin_panel.php" class="btn back">← Back</a>
    <h2>Edit Account</h2>
    <form method="post">
      <input type="text" name="full_name" value="<?= htmlspecialchars($editUser['full_name']) ?>" required>
      <input type="email" name="email" value="<?= htmlspecialchars($editUser['email']) ?>" required>
      <select name="role" required>
        <option value="user" <?= $editUser['role'] === 'user' ? 'selected' : '' ?>>User</option>
        <option value="admin" <?= $editUser['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
      <button class="btn primary" style="width:100%">Update Account</button>
    </form>
  </div>
</body>
</html>
