<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['user_id'];
    $receiver_id = intval($_POST['receiver_id']);
    $message_text = trim($_POST['message_text']);
    
    if (!empty($message_text)) {
        // Insert message using your database structure
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message_text, created_at, seen, is_read) VALUES (?, ?, ?, NOW(), 0, 0)");
        $stmt->bind_param("iis", $sender_id, $receiver_id, $message_text);
        
        if ($stmt->execute()) {
            echo "Message sent successfully";
        } else {
            echo "Error sending message";
        }
        
        $stmt->close();
    }
}
?>