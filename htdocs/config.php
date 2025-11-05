<?php
// Start session safely (only once)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Database connection function (singleton)
if (!function_exists('dbConnect')) {

    function dbConnect() {
        static $pdo = null;

        if ($pdo === null) {
            // 🔧 Database configuration
            $DB_HOST = '127.0.0.1';
            $DB_NAME = 'planmystudy'; // your database name
            $DB_USER = 'root';
            $DB_PASS = '';

            $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

            try {
                $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // ❌ Stop execution on connection failure
                die("<strong>Database connection failed:</strong> " . htmlspecialchars($e->getMessage()));
            }
        }

        return $pdo;
    }

    // ✅ Check login status
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    // ✅ Get current logged-in user
    function currentUser() {
        $pdo = dbConnect();

        if (!isLoggedIn()) {
            return null;
        }

        $stmt = $pdo->prepare("SELECT id, full_name, email, role, dark_mode FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }

    // ✅ Check if current user is admin
    function isAdmin() {
        $user = currentUser();
        return $user && strtolower($user['role']) === 'admin';
    }

    // ✅ Check if current user is student
    function isStudent() {
        $user = currentUser();
        return $user && strtolower($user['role']) === 'student';
    }
}

// Global PDO instance (for all other files)
$pdo = dbConnect();
?>
