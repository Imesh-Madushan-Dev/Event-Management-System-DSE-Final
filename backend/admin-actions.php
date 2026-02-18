<?php
/**
 * Admin Actions Handler
 * ---------------------
 * POST actions: delete_user
 */

session_start();
require_once 'db.php';

header('Content-Type: application/json');

// ── Auth guard ──────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_user') {
    $user_id = intval($_POST['user_id'] ?? 0);

    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        exit();
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM Users WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $user_id);

    if (mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
