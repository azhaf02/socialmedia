<?php
session_start();
header('Content-Type: application/json');
require 'config.php';
if (!isset($_SESSION['user_id'])) { echo json_encode(['count'=>0]); exit(); }
$uid = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND seen = 0");
$stmt->bind_param("i", $uid);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();
echo json_encode(['count' => intval($res['cnt'])]);
exit();
