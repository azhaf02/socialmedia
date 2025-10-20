<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit("Unauthorized");
}

$uid = $_SESSION['user_id'];
$partner = intval($_GET['user']);

// Get current user info
$user_sql = "SELECT username, profile_pic FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $uid);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_pic = $user_data['profile_pic'] ?? 'default_avatar.jpg';
$user_stmt->close();

// Get partner info
$partner_sql = "SELECT username, profile_pic FROM users WHERE id = ?";
$partner_stmt = $conn->prepare($partner_sql);
$partner_stmt->bind_param("i", $partner);
$partner_stmt->execute();
$partner_result = $partner_stmt->get_result();
$partner_data = $partner_result->fetch_assoc();
$partner_pic = $partner_data['profile_pic'] ?? 'default_avatar.jpg';
$partner_name = $partner_data['username'] ?? 'User';
$partner_stmt->close();

// Fetch messages between current user and partner using your database structure
$sql = "SELECT m.*, u.username, u.profile_pic 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
           OR (m.sender_id = ? AND m.receiver_id = ?) 
        ORDER BY m.created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $uid, $partner, $partner, $uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div style="text-align: center; color: #666; padding: 40px 20px; font-size: 14px;">';
    echo 'No messages yet. Start the conversation!';
    echo '</div>';
} else {
    while ($row = $result->fetch_assoc()) {
        $isMe = ($row['sender_id'] == $uid);
        $messageClass = $isMe ? 'me' : 'other';
        $time = date('H:i', strtotime($row['created_at']));
        
        // Use message_text instead of message
        $message_content = isset($row['message_text']) ? htmlspecialchars($row['message_text']) : '';
        
        if (empty($message_content)) continue;
        
        echo '<div class="message ' . $messageClass . '">';
        
        // Avatar
        $avatar_src = $isMe ? $user_pic : $partner_pic;
        echo '<img src="' . htmlspecialchars($avatar_src) . '" class="message-avatar" alt="' . ($isMe ? 'You' : htmlspecialchars($row['username'])) . '">';
        
        echo '<div class="message-content">';
        echo '<div class="message-bubble">' . $message_content . '</div>';
        echo '<div class="message-info">';
        echo '<span class="message-time">' . $time . '</span>';
        if ($isMe) {
            $status = $row['seen'] ? '✓✓' : '✓';
            echo '<span class="message-status">' . $status . '</span>';
        }
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
    }
}

$stmt->close();
?>