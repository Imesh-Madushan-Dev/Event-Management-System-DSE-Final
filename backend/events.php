<?php
/**
 * Events CRUD Handler (Admin Only)
 * ---------------------------------
 * POST: create | update | delete
 * GET:  get (single event by id)
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

$admin_id = intval($_SESSION['user_id']);

// ── Helper: send JSON response and exit ─────────
function respond(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit();
}

// ══════════════════════════════════════════════════
// POST actions
// ══════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── Create Event ────────────────────────────
    if ($action === 'create') {
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $img_url     = trim($_POST['img_url'] ?? '');
        $price       = floatval($_POST['price'] ?? 0);
        $branch      = trim($_POST['branch'] ?? '');

        if (empty($name) || empty($branch)) {
            respond(false, 'Event name and branch are required');
        }

        $stmt = mysqli_prepare($conn,
            "INSERT INTO Events (admin_id, name, description, img_url, price, branch)
             VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isssds', $admin_id, $name, $description, $img_url, $price, $branch);

        if (mysqli_stmt_execute($stmt)) {
            respond(true, 'Event created successfully');
        }
        respond(false, 'Failed to create event');
    }

    // ── Update Event ────────────────────────────
    if ($action === 'update') {
        $event_id    = intval($_POST['event_id'] ?? 0);
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $img_url     = trim($_POST['img_url'] ?? '');
        $price       = floatval($_POST['price'] ?? 0);
        $branch      = trim($_POST['branch'] ?? '');

        $stmt = mysqli_prepare($conn,
            "UPDATE Events SET name=?, description=?, img_url=?, price=?, branch=?
             WHERE event_id=?");
        mysqli_stmt_bind_param($stmt, 'sssdsi', $name, $description, $img_url, $price, $branch, $event_id);

        if (mysqli_stmt_execute($stmt)) {
            respond(true, 'Event updated successfully');
        }
        respond(false, 'Failed to update event');
    }

    // ── Delete Event ────────────────────────────
    if ($action === 'delete') {
        $event_id = intval($_POST['event_id'] ?? 0);

        $stmt = mysqli_prepare($conn, "DELETE FROM Events WHERE event_id=?");
        mysqli_stmt_bind_param($stmt, 'i', $event_id);

        if (mysqli_stmt_execute($stmt)) {
            respond(true, 'Event deleted successfully');
        }
        respond(false, 'Failed to delete event');
    }

    respond(false, 'Invalid action');
}

// ══════════════════════════════════════════════════
// GET: fetch single event
// ══════════════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'get') {
    $event_id = intval($_GET['id'] ?? 0);

    $stmt = mysqli_prepare($conn, "SELECT * FROM Events WHERE event_id=?");
    mysqli_stmt_bind_param($stmt, 'i', $event_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $event  = mysqli_fetch_assoc($result);

    if ($event) {
        echo json_encode(['success' => true, 'event' => $event]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Event not found']);
    }
    exit();
}
