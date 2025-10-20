<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$follower_id = $_SESSION['user_id'];
$following_id = intval($_GET['id']);

if ($follower_id != $following_id) {
    $stmt = $conn->prepare("INSERT INTO followers (follower_id, following_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $follower_id, $following_id);
    $stmt->execute();
}

header("Location: search.php");
exit();
?>
