<?php
/**
 * User Actions Handler
 * --------------------
 * POST actions: toggle_like | toggle_attendance | buy_ticket
 * All responses are JSON.
 */

session_start();
require_once 'db.php';

header('Content-Type: application/json');

// ── Auth guard ──────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = intval($_SESSION['user_id']);

// ── Helper: send JSON response and exit ─────────
function respond(bool $success, string $message, array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed');
}

$action = $_POST['action'] ?? '';

// ══════════════════════════════════════════════════
// Toggle Like
// ══════════════════════════════════════════════════
if ($action === 'toggle_like') {
    $event_id = intval($_POST['event_id'] ?? 0);

    // Check existing like
    $check = mysqli_prepare($conn, "SELECT 1 FROM Event_Likes WHERE event_id=? AND user_id=?");
    mysqli_stmt_bind_param($check, 'ii', $event_id, $user_id);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);
    $already_liked = mysqli_stmt_num_rows($check) > 0;
    mysqli_stmt_close($check);

    if ($already_liked) {
        $stmt = mysqli_prepare($conn, "DELETE FROM Event_Likes WHERE event_id=? AND user_id=?");
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO Event_Likes (event_id, user_id) VALUES (?, ?)");
    }
    mysqli_stmt_bind_param($stmt, 'ii', $event_id, $user_id);

    if (!mysqli_stmt_execute($stmt)) {
        respond(false, 'Failed to update like');
    }

    // Return updated count
    $count_stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS cnt FROM Event_Likes WHERE event_id=?");
    mysqli_stmt_bind_param($count_stmt, 'i', $event_id);
    mysqli_stmt_execute($count_stmt);
    $count = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['cnt'];

    respond(true, 'Like updated', ['liked' => !$already_liked, 'count' => (int) $count]);
}

// ══════════════════════════════════════════════════
// Toggle Attendance
// ══════════════════════════════════════════════════
if ($action === 'toggle_attendance') {
    $event_id = intval($_POST['event_id'] ?? 0);

    $check = mysqli_prepare($conn, "SELECT 1 FROM Event_Attendance WHERE event_id=? AND user_id=?");
    mysqli_stmt_bind_param($check, 'ii', $event_id, $user_id);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);
    $already_attending = mysqli_stmt_num_rows($check) > 0;
    mysqli_stmt_close($check);

    if ($already_attending) {
        $stmt = mysqli_prepare($conn, "DELETE FROM Event_Attendance WHERE event_id=? AND user_id=?");
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO Event_Attendance (event_id, user_id) VALUES (?, ?)");
    }
    mysqli_stmt_bind_param($stmt, 'ii', $event_id, $user_id);

    if (!mysqli_stmt_execute($stmt)) {
        respond(false, 'Failed to update attendance');
    }

    $count_stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS cnt FROM Event_Attendance WHERE event_id=?");
    mysqli_stmt_bind_param($count_stmt, 'i', $event_id);
    mysqli_stmt_execute($count_stmt);
    $count = mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['cnt'];

    respond(true, 'Attendance updated', ['attending' => !$already_attending, 'count' => (int) $count]);
}

// ══════════════════════════════════════════════════
// Buy Ticket
// ══════════════════════════════════════════════════
if ($action === 'buy_ticket') {
    $event_id = intval($_POST['event_id'] ?? 0);

    // Check duplicate ticket
    $dup = mysqli_prepare($conn, "SELECT 1 FROM Tickets WHERE event_id=? AND user_id=?");
    mysqli_stmt_bind_param($dup, 'ii', $event_id, $user_id);
    mysqli_stmt_execute($dup);
    mysqli_stmt_store_result($dup);
    if (mysqli_stmt_num_rows($dup) > 0) {
        respond(false, 'You already have a ticket for this event');
    }
    mysqli_stmt_close($dup);

    // Fetch event
    $ev = mysqli_prepare($conn, "SELECT * FROM Events WHERE event_id=?");
    mysqli_stmt_bind_param($ev, 'i', $event_id);
    mysqli_stmt_execute($ev);
    $event = mysqli_fetch_assoc(mysqli_stmt_get_result($ev));

    if (!$event) {
        respond(false, 'Event not found');
    }

    // Generate unique ticket code
    $ticket_code = 'NIBM' . str_pad($event_id, 3, '0', STR_PAD_LEFT)
                 . str_pad($user_id, 3, '0', STR_PAD_LEFT)
                 . time();
    $price = floatval($event['price']);

    $ins = mysqli_prepare($conn,
        "INSERT INTO Tickets (event_id, user_id, ticket_code, price) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($ins, 'iisd', $event_id, $user_id, $ticket_code, $price);

    if (mysqli_stmt_execute($ins)) {
        respond(true, 'Ticket booked successfully!', [
            'ticket_code'         => $ticket_code,
            'ticket_id'           => mysqli_insert_id($conn),
            'event_name'          => $event['name'],
            'price'               => $price,
            'is_free'             => $price == 0,
            'redirect_to_checkout' => true,
        ]);
    }
    respond(false, 'Failed to book ticket');
}

respond(false, 'Invalid action');
