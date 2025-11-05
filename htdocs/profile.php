<?php
require 'config.php';
if (!isLoggedIn()) header("Location: login.php");
$user = currentUser();
$pdo = dbConnect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'] ?? $user['full_name'];
  $dark = isset($_POST['dark']) ? 1 : 0;
  $pdo->prepare("UPDATE users SET full_name = ?, dark_mode = ? WHERE id = ?")->execute([$name, $dark, $user['id']]);
  header("Location: profile.php?saved=1");
  exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Profile | PlanMyStudy</title>
<link rel="stylesheet" href="style.css">
<style>
/* ==== NAVBAR ==== */
header {
  background: #1a2b4a;
  color: white;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  padding: 10px 0;
  position: sticky;
  top: 0;
  z-index: 1000;
}
.nav-container {
  width: 90%;
  max-width: 1100px;
  margin: auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.logo {
  display: flex;
  align-items: center;
  gap: 10px;
}
.logo img {
  width: 40px;
  height: 40px;
  border-radius: 50%;
}
.logo span {
  font-weight: 700;
  font-size: 20px;
  color: white;
}
nav a {
  text-decoration: none;
  color: white;
  font-weight: 500;
  margin-left: 18px;
  transition: 0.2s;
}
nav a:hover, nav a.active {
  color: #1fb14b;
}

/* ==== BODY ==== */
body {
  background: #ffffff;
  font-family: "Segoe UI", Roboto, Arial, sans-serif;
  margin: 0;
  color: #1a2b4a;
}

/* ==== CONTAINER ==== */
.container {
  width: 90%;
  max-width: 700px;
  margin: 60px auto;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  padding: 40px;
  text-align: center;
}

/* ==== PROFILE IMAGE ==== */
.profile-pic {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  border: 3px solid #1fb14b;
  object-fit: cover;
  margin-bottom: 15px;
}

/* ==== HEADINGS ==== */
h1 {
  font-size: 26px;
  margin-bottom: 5px;
  color: #1a2b4a;
}
.muted {
  color: #777;
  margin-bottom: 30px;
}

/* ==== FORM ==== */
form {
  text-align: left;
  margin-top: 15px;
}
label {
  display: block;
  font-weight: 600;
  margin-bottom: 5px;
  color: #1a2b4a;
}
input[type="text"], input[type="email"] {
  width: 100%;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #ccc;
  margin-bottom: 18px;
  font-size: 15px;
  box-sizing: border-box;
}

/* ==== DARK MODE CHECKBOX ALIGNMENT ==== */
.darkmode-toggle {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}
.darkmode-toggle input[type="checkbox"] {
  width: 18px;
  height: 18px;
  accent-color: #1fb14b;
}
.darkmode-toggle label {
  margin: 0;
  font-weight: 600;
  color: #1a2b4a;
}

/* ==== BUTTONS ==== */
.btn {
  background: #1fb14b;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px 20px;
  cursor: pointer;
  font-weight: 600;
  transition: 0.2s;
}
.btn:hover { background: #158c3a; }
.btn.danger {
  background: #b32626;
}
.btn.danger:hover {
  background: #922020;
}

/* ==== ALERT ==== */
.alert.success {
  background: #d6f5dc;
  color: #1b5e20;
  padding: 10px;
  border-radius: 6px;
  margin-bottom: 15px;
  text-align: center;
}
</style>
</head>
<body class="<?= $user['dark_mode'] ? 'dark' : '' ?>">

<header>
  <div class="nav-container">
    <div class="logo">
      <img src="/image/pms.png" alt="PlanMyStudy Logo" class="pms-logo">

      <span>PlanMyStudy</span>
    </div>
    <nav>
      <a href="home.php">Home</a>
      <a href="profile.php" class="active">Profile</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<main class="container">
  <?php if(!empty($_GET['saved'])): ?>
    <div class="alert success">✅ Profile updated successfully!</div>
  <?php endif; ?>

  <img src="/image/cat.png" alt="Profile Picture" class="profile-pic">

  <h1><?= htmlspecialchars($user['full_name']) ?></h1>
  <p class="muted"><?= htmlspecialchars($user['email']) ?></p>

  <form method="post">
    <label>Full Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($user['full_name']) ?>" required>

    <label>Email</label>
    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>

    <div class="darkmode-toggle">
      <input type="checkbox" name="dark" id="darkMode" <?= $user['dark_mode'] ? 'checked' : '' ?>>
      <label for="darkMode">Enable Dark Mode</label>
    </div>

    <button class="btn" type="submit">Save Changes</button>
  </form>

  <div style="margin-top: 30px;">
    <a href="forgot_password.php" class="btn">Change Password</a>
    <form method="post" action="delete_account.php" style="display:inline;" onsubmit="return confirm('Delete this account?');">
      <button class="btn danger">Delete Account</button>
    </form>
  </div>
</main>

</body>
</html>
