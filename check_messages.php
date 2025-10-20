<?php
session_start();
include 'config.php';
if(!isset($_SESSION['user_id'])) exit;

$user_id = $_SESSION['user_id'];

// Fetch unread messages
$sql = "SELECT m.id, m.message, u.username 
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.receiver_id = ? AND m.is_read = 0
        ORDER BY m.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);
?>
