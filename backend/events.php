<?php
session_start();
require_once 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$admin_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'create') {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $img_url = mysqli_real_escape_string($conn, $_POST['img_url']);
        $price = floatval($_POST['price']);
        $branch = mysqli_real_escape_string($conn, $_POST['branch']);
        
        $query = "INSERT INTO Events (admin_id, name, description, img_url, price, branch) 
                  VALUES ($admin_id, '$name', '$description', '$img_url', $price, '$branch')";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Event created successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create event']);
        }
    }
    
    elseif ($action === 'update') {
        $event_id = intval($_POST['event_id']);
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $img_url = mysqli_real_escape_string($conn, $_POST['img_url']);
        $price = floatval($_POST['price']);
        $branch = mysqli_real_escape_string($conn, $_POST['branch']);
        
        $query = "UPDATE Events SET name='$name', description='$description', img_url='$img_url', 
                  price=$price, branch='$branch' WHERE event_id=$event_id AND admin_id=$admin_id";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update event']);
        }
    }
    
    elseif ($action === 'delete') {
        $event_id = intval($_POST['event_id']);
        
        $query = "DELETE FROM Events WHERE event_id=$event_id AND admin_id=$admin_id";
        
        if (mysqli_query($conn, $query)) {
            echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete event']);
        }
    }
}

elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'get' && isset($_GET['id'])) {
        $event_id = intval($_GET['id']);
        $query = "SELECT * FROM Events WHERE event_id=$event_id AND admin_id=$admin_id";
        $result = mysqli_query($conn, $query);
        
        if ($event = mysqli_fetch_assoc($result)) {
            echo json_encode(['success' => true, 'event' => $event]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Event not found']);
        }
    }
}
?>
