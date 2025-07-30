<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'register') {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $user_type = $_POST['user_type'];
        
        // Check if email already exists
        $check_query = $user_type === 'admin' ? 
            "SELECT * FROM Admins WHERE email = '$email'" : 
            "SELECT * FROM Users WHERE email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit();
        }
        
        // Insert new user
        $insert_query = $user_type === 'admin' ? 
            "INSERT INTO Admins (name, email, password) VALUES ('$name', '$email', '$password')" :
            "INSERT INTO Users (name, email, password) VALUES ('$name', '$email', '$password')";
        
        if (mysqli_query($conn, $insert_query)) {
            echo json_encode(['success' => true, 'message' => 'Registration successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registration failed']);
        }
    }
    
    elseif ($action === 'login') {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];
        $user_type = $_POST['user_type'];
        
        // Check credentials
        $login_query = $user_type === 'admin' ? 
            "SELECT * FROM Admins WHERE email = '$email'" : 
            "SELECT * FROM Users WHERE email = '$email'";
        $login_result = mysqli_query($conn, $login_query);
        
        if (mysqli_num_rows($login_result) === 1) {
            $user = mysqli_fetch_assoc($login_result);
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user_type === 'admin' ? $user['admin_id'] : $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = $user_type;
                
                $redirect_url = $user_type === 'admin' ? 'admin-dashboard.php' : 'user-dashboard.php';
                echo json_encode(['success' => true, 'redirect' => $redirect_url]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid password']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    }
}

elseif (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: ../index.html');
    exit();
}
?>
