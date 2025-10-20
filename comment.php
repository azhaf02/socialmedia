<?php
// comment.php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: feed.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$post_id = intval($_POST['post_id'] ?? 0);
$text = trim($_POST['text'] ?? '');
if ($text === '' || strlen($text) > 1000) {
    header("Location: feed.php");
    exit();
}

// verify post exists and owner
$stmt = $conn->prepare("SELECT id, user_id FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows == 0) {
    $stmt->close();
    header("Location: feed.php");
    exit();
}
$stmt->bind_result($pid, $postOwner); $stmt->fetch(); $stmt->close();

// insert comment
$ins = $conn->prepare("INSERT INTO comments (user_id, post_id, text) VALUES (?, ?, ?)");
$ins->bind_param("iis", $user_id, $post_id, $text);
$ins->execute();
$commentId = $ins->insert_id;
$ins->close();

// notify post owner (if commenter != owner)
if ($postOwner != $user_id) {
    $ntxt = "commented: " . (strlen($text) > 100 ? substr($text,0,97).'...' : $text);
    $n = $conn->prepare("INSERT INTO notifications (user_id, sender_id, type, reference_id, text) VALUES (?, ?, 'comment', ?, ?)");
    $n->bind_param("iiis", $postOwner, $user_id, $post_id, $ntxt);
    $n->execute();
    $n->close();
}

header("Location: feed.php");
exit();
