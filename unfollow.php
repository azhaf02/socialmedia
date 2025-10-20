<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['following_id'])) {
    $follower_id = $_SESSION['user_id'];
    $following_id = $_POST['following_id'];

    $stmt = $conn->prepare("DELETE FROM followers WHERE follower_id=? AND following_id=?");
    $stmt->bind_param("ii", $follower_id, $following_id);
    $stmt->execute();
}
header("Location: search.php?q=");
exit();
