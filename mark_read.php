<?php
include 'config.php';
$data = json_decode(file_get_contents("php://input"), true);

if(isset($data['message_id'])){
    $message_id = $data['message_id'];
    $sql = "UPDATE messages SET is_read = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
}
?>
