<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'toggle_like') {
        $event_id = intval($_POST['event_id']);
        
        // Check if already liked
        $check_query = "SELECT * FROM Event_Likes WHERE event_id=$event_id AND user_id=$user_id";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Unlike
            $query = "DELETE FROM Event_Likes WHERE event_id=$event_id AND user_id=$user_id";
            $liked = false;
        } else {
            // Like
            $query = "INSERT INTO Event_Likes (event_id, user_id) VALUES ($event_id, $user_id)";
            $liked = true;
        }
        
        if (mysqli_query($conn, $query)) {
            // Get updated like count
            $count_query = "SELECT COUNT(*) as count FROM Event_Likes WHERE event_id=$event_id";
            $count_result = mysqli_query($conn, $count_query);
            $count = mysqli_fetch_assoc($count_result)['count'];
            
            echo json_encode(['success' => true, 'liked' => $liked, 'count' => $count]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update like']);
        }
    }
    
    elseif ($action === 'toggle_attendance') {
        $event_id = intval($_POST['event_id']);
        
        // Check if already attending
        $check_query = "SELECT * FROM Event_Attendance WHERE event_id=$event_id AND user_id=$user_id";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Remove attendance
            $query = "DELETE FROM Event_Attendance WHERE event_id=$event_id AND user_id=$user_id";
            $attending = false;
        } else {
            // Add attendance
            $query = "INSERT INTO Event_Attendance (event_id, user_id) VALUES ($event_id, $user_id)";
            $attending = true;
        }
        
        if (mysqli_query($conn, $query)) {
            // Get updated attendance count
            $count_query = "SELECT COUNT(*) as count FROM Event_Attendance WHERE event_id=$event_id";
            $count_result = mysqli_query($conn, $count_query);
            $count = mysqli_fetch_assoc($count_result)['count'];
            
            echo json_encode(['success' => true, 'attending' => $attending, 'count' => $count]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update attendance']);
        }
    }
    
    elseif ($action === 'buy_ticket') {
        $event_id = intval($_POST['event_id']);
        
        // Check if user already has a ticket for this event
        $existing_ticket_query = "SELECT * FROM Tickets WHERE event_id=$event_id AND user_id=$user_id";
        $existing_result = mysqli_query($conn, $existing_ticket_query);
        
        if (mysqli_num_rows($existing_result) > 0) {
            echo json_encode(['success' => false, 'message' => 'You already have a ticket for this event']);
            exit();
        }
        
        // Get event details
        $event_query = "SELECT * FROM Events WHERE event_id=$event_id";
        $event_result = mysqli_query($conn, $event_query);
        $event = mysqli_fetch_assoc($event_result);
        
        if (!$event) {
            echo json_encode(['success' => false, 'message' => 'Event not found']);
            exit();
        }
        
        // Generate unique ticket code
        $ticket_code = 'NIBM' . str_pad($event_id, 3, '0', STR_PAD_LEFT) . str_pad($user_id, 3, '0', STR_PAD_LEFT) . time();
        
        // Insert ticket
        $price = floatval($event['price']);
        $query = "INSERT INTO Tickets (event_id, user_id, ticket_code, price) 
              VALUES ($event_id, $user_id, '$ticket_code', $price)";
        
        if (mysqli_query($conn, $query)) {
            $ticket_id = mysqli_insert_id($conn);
            echo json_encode([
                'success' => true, 
                'message' => 'Ticket booked successfully!', 
                'ticket_code' => $ticket_code,
                'ticket_id' => $ticket_id,
                'event_name' => $event['name'],
                'price' => $price,
                'is_free' => $price == 0,
                'redirect_to_checkout' => true
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to book ticket: ' . mysqli_error($conn)]);
        }
    }
}
?>
