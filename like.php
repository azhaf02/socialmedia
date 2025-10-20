<?php
// like.php
session_start();
header('Content-Type: application/json');
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Not authenticated']);
    exit();
}
$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['post_id'])) {
    echo json_encode(['success'=>false,'error'=>'Invalid request']);
    exit();
}
$post_id = intval($_POST['post_id']);

// verify post and owner
$stmt = $conn->prepare("SELECT id, user_id FROM posts WHERE id = ?");
$stmt->bind_param("i",$post_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows == 0) {
    echo json_encode(['success'=>false,'error'=>'Post not found']);
    exit();
}
$post = $res->fetch_assoc();
$postOwner = intval($post['user_id']);
$stmt->close();

// toggle like
$stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
$stmt->bind_param("ii", $user_id, $post_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    // already liked -> remove
    $stmt->bind_result($likeId);
    $stmt->fetch();
    $stmt->close();
    $del = $conn->prepare("DELETE FROM likes WHERE id = ?");
    $del->bind_param("i",$likeId);
    $del->execute();
    $del->close();
    $action = 'unliked';
    // Optionally remove related notification(s) for this like
    $deln = $conn->prepare("DELETE FROM notifications WHERE type='like' AND sender_id=? AND reference_id=? AND user_id=?");
    $deln->bind_param("iii", $user_id, $post_id, $postOwner);
    $deln->execute();
    $deln->close();
} else {
    $stmt->close();
    $ins = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
    $ins->bind_param("ii", $user_id, $post_id);
    $ins->execute();
    $ins->close();
    $action = 'liked';

    // Create notification for post owner (if liker != owner)
    if ($postOwner !== $user_id) {
        $text = "liked your post";
        $n = $conn->prepare("INSERT INTO notifications (user_id, sender_id, type, reference_id, text) VALUES (?, ?, 'like', ?, ?)");
        $n->bind_param("iiis", $postOwner, $user_id, $post_id, $text);
        $n->execute();
        $n->close();
    }
}

// new likes count
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM likes WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$likes_count = intval($res['cnt']);
$stmt->close();

echo json_encode(['success'=>true, 'action'=>$action, 'likes_count'=>$likes_count]);
exit();
