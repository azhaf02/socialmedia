<?php
// fetch_explore.php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) { http_response_code(403); exit(); }
$perPage = 5;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// fetch public latest posts (all users)
$stmt = $conn->prepare("SELECT posts.*, users.username, users.profile_pic
                       FROM posts
                       JOIN users ON posts.user_id = users.id
                       ORDER BY posts.created_at DESC
                       LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$res = $stmt->get_result();
$html = "";

while ($post = $res->fetch_assoc()) {
    $pId = intval($post['id']);
    // likes count
    $lq = $conn->prepare("SELECT COUNT(*) as cnt FROM likes WHERE post_id = ?");
    $lq->bind_param("i", $pId); $lq->execute(); $lc = $lq->get_result()->fetch_assoc()['cnt']; $lq->close();
    // is liked
    $li = $conn->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
    $li->bind_param("ii", $pId, $_SESSION['user_id']); $li->execute(); $li->store_result();
    $isLiked = $li->num_rows > 0; $li->close();

    $html .= '<div class="post">';
    $html .= '<div style="display:flex;gap:10px;align-items:center;"><img src="'.htmlspecialchars($post['profile_pic'] ?: 'uploads/default.png').'" style="width:48px;height:48px;border-radius:50%;">';
    $html .= '<div><strong>'.htmlspecialchars($post['username']).'</strong><br><small style="color:#666">'.$post['created_at'].'</small></div></div>';
    if (!empty($post['content'])) $html .= '<p>'.nl2br(htmlspecialchars($post['content'])).'</p>';
    if (!empty($post['media_url'])) {
        $ext = strtolower(pathinfo($post['media_url'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif'])) $html .= '<img src="'.htmlspecialchars($post['media_url']).'" style="max-width:100%;border-radius:8px;">';
        else $html .= '<video controls style="max-width:100%;border-radius:8px;"><source src="'.htmlspecialchars($post['media_url']).'"></video>';
    }
    $html .= '<div style="margin-top:8px;"><button class="like-btn" data-post-id="'.$pId.'">'.($isLiked ? 'Unlike' : 'Like').' ( <span class="likes-count">'.$lc.'</span> )</button>';
    $html .= ' <a href="profile.php?id='.$post['user_id'].'">View</a></div>';
    $html .= '</div>';
}

echo $html;
exit();
