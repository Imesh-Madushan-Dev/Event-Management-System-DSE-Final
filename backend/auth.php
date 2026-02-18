<?php
/**
 * Authentication Handler
 * ----------------------
 * Handles user/admin registration, login, and logout.
 * Responds with JSON for POST requests; redirects for logout.
 */

session_start();
require_once 'db.php';

header('Content-Type: application/json');

// ── Helper: send JSON response and exit ─────────
function respond(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit();
}

// ══════════════════════════════════════════════════
// POST actions: register | login
// ══════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Register ────────────────────────────────
    if ($action === 'register') {
        $name      = trim($_POST['name']  ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password']   ?? '';
        $user_type = $_POST['user_type']  ?? 'user';

        // Basic validation
        if (empty($name) || empty($email) || empty($password)) {
            respond(false, 'All fields are required');
        }

        $table = ($user_type === 'admin') ? 'Admins' : 'Users';

        // Check for existing email
        $check = mysqli_prepare($conn, "SELECT 1 FROM $table WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($check, 's', $email);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            respond(false, 'Email already exists');
        }
        mysqli_stmt_close($check);

        // Insert new account
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $insert = mysqli_prepare($conn, "INSERT INTO $table (name, email, password) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($insert, 'sss', $name, $email, $hashed);

        if (mysqli_stmt_execute($insert)) {
            respond(true, 'Registration successful');
        }
        respond(false, 'Registration failed. Please try again.');
    }

    // ── Login ───────────────────────────────────
    if ($action === 'login') {
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password']   ?? '';
        $user_type = $_POST['user_type']  ?? 'user';

        if (empty($email) || empty($password)) {
            respond(false, 'Email and password are required');
        }

        $table    = ($user_type === 'admin') ? 'Admins' : 'Users';
        $id_col   = ($user_type === 'admin') ? 'admin_id' : 'user_id';

        $stmt = mysqli_prepare($conn, "SELECT * FROM $table WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);

        if (!$user) {
            respond(false, 'User not found');
        }

        if (!password_verify($password, $user['password'])) {
            respond(false, 'Invalid password');
        }

        // Set session
        $_SESSION['user_id']   = $user[$id_col];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type'] = $user_type;

        $redirect = ($user_type === 'admin') ? 'admin-dashboard.php' : 'user-dashboard.php';
        respond(true, 'Login successful', ['redirect' => $redirect]);
    }

    respond(false, 'Invalid action');
}

// ══════════════════════════════════════════════════
// GET action: logout
// ══════════════════════════════════════════════════
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: ../index.html');
    exit();
}
