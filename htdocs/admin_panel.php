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

// Fetch all users
$stmt = $pdo->query("SELECT id, user_code, full_name, email, role FROM users ORDER BY id ASC");
$allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Panel | PlanMyStudy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* ===== NAVBAR ===== */
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

/* ===== BODY ===== */
body {
  background: #ffffff;
  font-family: "Segoe UI", Roboto, Arial, sans-serif;
  margin: 0;
  color: #1a2b4a;
}

/* ===== CONTAINER ===== */
.container {
  width: 90%;
  max-width: 1000px;
  margin: 60px auto;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  padding: 40px;
}

/* ===== HEADINGS ===== */
h2 {
  text-align: center;
  color: #1a2b4a;
  margin-bottom: 10px;
}
.muted {
  color: #777;
  text-align: center;
  margin-bottom: 25px;
}

/* ===== TABLE ===== */
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}
th, td {
  border: 1px solid #ddd;
  padding: 12px;
  text-align: center;
}
th {
  background: #1a2b4a;
  color: #fff;
  font-weight: 600;
}
tr:nth-child(even) {
  background: #f4f9f5;
}
tr:hover {
  background: #e6f5eb;
}

/* ===== BUTTONS ===== */
.action-btns a {
  text-decoration: none;
  padding: 6px 12px;
  border-radius: 6px;
  font-size: 0.9rem;
  margin: 0 3px;
  display: inline-block;
}
.edit {
  background: #1fb14b;
  color: white;
}
.delete {
  background: #b32626;
  color: white;
}
.edit:hover {
  background: #158c3a;
}
.delete:hover {
  background: #922020;
}
.logout-btn {
  float: right;
  text-decoration: none;
  background: #b32626;
  color: #fff;
  padding: 8px 14px;
  border-radius: 6px;
  transition: 0.2s;
}
.logout-btn:hover {
  background: #922020;
}

/* ===== MOBILE ===== */
@media (max-width: 768px) {
  table, thead, tbody, th, td, tr {
    display: block;
  }
  th {
    display: none;
  }
  td {
    text-align: left;
    padding-left: 50%;
    position: relative;
  }
  td::before {
    content: attr(data-label);
    position: absolute;
    left: 15px;
    width: 45%;
    font-weight: bold;
  }
}
</style>
</head>
<body>

<header>
  <div class="nav-container">
    <div class="logo">
      <img src="/image/pms.png" alt="PlanMyStudy Logo" class="pms-logo">
      <span>PlanMyStudy</span>
    </div>
    <nav>
     
      <a href="admin_panel.php" class="active">Admin Panel</a>
      <a href="logout.php">Logout</a>
    </nav>
  </div>
</header>

<div class="container">
  <h2>Administrator Dashboard</h2>
  <p class="muted">Manage user accounts, roles, and details.</p>

  <table>
    <tr>
      <th>User Code</th>
      <th>Full Name</th>
      <th>Email</th>
      <th>Role</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($allUsers as $u): ?>
    <tr>
      <td data-label="User Code"><?= htmlspecialchars($u['user_code']) ?></td>
      <td data-label="Full Name"><?= htmlspecialchars($u['full_name']) ?></td>
      <td data-label="Email"><?= htmlspecialchars($u['email']) ?></td>
      <td data-label="Role"><?= htmlspecialchars(ucfirst($u['role'])) ?></td>
      <td class="action-btns" data-label="Actions">
        <a class="edit" href="edit_user.php?id=<?= $u['id'] ?>">Edit</a>
        <a class="delete" href="delete_user.php?id=<?= $u['id'] ?>" onclick="return confirm('Are you sure you want to delete this account?');">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

</body>
</html>
