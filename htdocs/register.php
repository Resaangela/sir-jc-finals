<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $role = strtolower(trim($_POST['role'] ?? 'user'));

    if ($name && filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($pass) >= 6) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        if (!in_array($role, ['user', 'admin'])) $role = 'user';

        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
            $stmt->execute([$role]);
            $count = $stmt->fetchColumn() + 1;

            $prefix = ($role === 'admin') ? 'ADMIN-' : 'USER-';
            $user_code = $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
            $student_id = ($role === 'user') ? 'STU-' . str_pad($count, 4, '0', STR_PAD_LEFT) : null;

            $stmt = $pdo->prepare("
                INSERT INTO users (full_name, email, password_hash, role, user_code, student_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $email, $hash, $role, $user_code, $student_id]);

            header("Location: login.php?registered=1");
            exit;
        } catch (PDOException $e) {
            $error = $e->getCode() == 23000 ? "Email already registered." : "Database error: " . $e->getMessage();
        }
    } else {
        $error = "Invalid input or password too short.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Create Account | PlanMyStudy</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* ==== BODY ==== */
body {
  background-color: #ffffff;
  font-family: "Segoe UI", Roboto, Arial, sans-serif;
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
}

/* ==== WRAPPER ==== */
.register-wrapper {
  background: #fff;
  width: 420px;
  border-radius: 16px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.15);
  padding: 40px 35px;
  text-align: center;
}

/* ==== LOGO ==== */
.logo {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-bottom: 15px;
}
.logo img {
  width: 45px;
  height: 45px;
  border-radius: 50%;
}
.logo span {
  font-size: 22px;
  font-weight: 700;
  color: #1a2b4a;
}

/* ==== TEXT ==== */
h2 {
  margin: 10px 0 5px;
  font-size: 26px;
  color: #1a2b4a;
}
p {
  color: #8693a5;
  margin-bottom: 25px;
}

/* ==== FORM ==== */
form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}
input, select {
  padding: 12px;
  font-size: 15px;
  border: 1px solid #d8dee6;
  border-radius: 8px;
  outline: none;
  transition: 0.2s;
}
input:focus, select:focus {
  border-color: #1fb14b;
  box-shadow: 0 0 4px rgba(31,177,75,0.4);
}

/* ==== BUTTON ==== */
.btn {
  padding: 12px;
  border: none;
  background: #1a2b4a;
  color: white;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: 0.2s;
}
.btn:hover {
  background: #1a2b4a;
}

/* ==== ALERT ==== */
.error {
  background: #fdecea;
  color: #b32626;
  padding: 10px;
  border-radius: 6px;
  margin-bottom: 15px;
}

/* ==== LINKS ==== */
.links {
  margin-top: 20px;
  text-align: center;
  font-size: 14px;
}
.links a {
  color: #1a2b4a;
  text-decoration: none;
  font-weight: 500;
}
.links a:hover {
  text-decoration: underline;
}

/* ==== MOBILE ==== */
@media (max-width: 480px) {
  .register-wrapper {
    width: 90%;
    padding: 30px 20px;
  }
}
</style>
</head>
<body>

<div class="register-wrapper">
  <div class="logo">
    <img src="/image/pms.png" alt="PlanMyStudy Logo" class="pms-logo">

    <span>PlanMyStudy</span>
  </div>

  <h2>Create Account</h2>
  <p>Join PlanMyStudy and organize your learning</p>

  <?php if(!empty($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post">
    <input name="name" placeholder="Full Name" required>
    <input name="email" placeholder="Email Address" type="email" required>
    <input name="password" placeholder="Password (min 6 chars)" type="password" required>
    <select name="role" required>
      <option value="">Select Role</option>
      <option value="user">User</option>
      <option value="admin">Admin</option>
    </select>
    <button class="btn" type="submit">Create Account</button>
  </form>

  <div class="links">
    <a href="login.php">Already have an account? Login</a>
  </div>
</div>

</body>
</html>
