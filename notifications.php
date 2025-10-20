<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$uid = $_SESSION['user_id'];

// mark single notification read if ?mark=ID
if (isset($_GET['mark'])) {
    $nid = intval($_GET['mark']);
    $stmt = $conn->prepare("UPDATE notifications SET seen = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $nid, $uid);
    $stmt->execute();
    $stmt->close();
    header("Location: notifications.php");
    exit();
}

// fetch recent notifications
$stmt = $conn->prepare("SELECT n.*, s.username AS sender_name, s.profile_pic AS sender_pic
                        FROM notifications n
                        LEFT JOIN users s ON n.sender_id = s.id
                        WHERE n.user_id = ?
                        ORDER BY n.created_at DESC
                        LIMIT 100");
$stmt->bind_param("i", $uid);
$stmt->execute();
$results = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Notifications</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .notif { background:#fff; margin:10px auto; max-width:800px; padding:12px; border-radius:8px; display:flex; gap:12px; align-items:center; }
    .muted { color:#666; }
    .unread { background:#eef6ff; }
  </style>
</head>
<body>
  <div class="container">
    <h2>Notifications</h2>
    <p><a href="feed.php">⬅ Back to Feed</a></p>
    <?php if ($results->num_rows == 0): ?>
      <p>No notifications.</p>
    <?php else: ?>
      <?php while($n = $results->fetch_assoc()): ?>
        <?php $cls = $n['seen'] ? '' : 'unread'; ?>
        <div class="notif <?php echo $cls; ?>">
          <div style="width:56px;">
            <img src="<?php echo htmlspecialchars($n['sender_pic'] ?: 'uploads/default.png'); ?>" style="width:48px;height:48px;border-radius:50%">
          </div>
          <div style="flex:1;">
            <strong><?php echo htmlspecialchars($n['sender_name'] ?: 'System'); ?></strong>
            <span class="muted"> — <?php echo htmlspecialchars($n['text']); ?></span>
            <div class="muted" style="font-size:12px;"><?php echo $n['created_at']; ?></div>
          </div>
          <div>
            <?php if (!$n['seen']): ?>
              <a href="notifications.php?mark=<?php echo $n['id']; ?>">Mark as read</a>
            <?php else: ?>
              <span class="muted">Read</span>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>
    <?php endif; ?>
  </div>
</body>
</html>
