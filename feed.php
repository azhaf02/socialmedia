<?php
// feed.php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];

// current user
$stmt = $conn->prepare("SELECT id, username, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$currentUser = $stmt->get_result()->fetch_assoc();
$stmt->close();

// posts
$sql = "SELECT posts.*, users.username, users.profile_pic
        FROM posts
        JOIN users ON posts.user_id = users.id
        WHERE posts.user_id = ?
           OR posts.user_id IN (SELECT following_id FROM followers WHERE follower_id = ?)
        ORDER BY posts.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$postsResult = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feed - Anas Insta</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    :root {
      --primary: #8B5CF6;
      --primary-dark: #7C3AED;
      --secondary: #F59E0B;
      --accent: #10B981;
      --dark: #0F0F13;
      --darker: #09090B;
      --light: #F8FAFC;
      --gray: #6B7280;
      --gray-dark: #374151;
      --card-bg: #1A1B23;
      --card-border: #2D2D3A;
      --error: #EF4444;
      --success: #10B981;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
    }

    body {
      background-color: var(--darker);
      color: var(--light);
      line-height: 1.6;
    }

    /* Navbar */
    .navbar {
      background-color: var(--dark);
      border-bottom: 1px solid var(--card-border);
      padding: 12px 20px;
      position: sticky;
      top: 0;
      z-index: 100;
      backdrop-filter: blur(10px);
    }

    .nav-container {
      max-width: 1000px;
      margin: 0 auto;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      font-size: 24px;
      font-weight: 700;
      display: flex;
      align-items: center;
    }

    .logo i {
      color: var(--primary);
      margin-right: 8px;
      font-size: 26px;
    }

    .logo span {
      color: var(--primary);
    }

    .nav-links {
      display: flex;
      gap: 20px;
      align-items: center;
    }

    .nav-link {
      color: var(--light);
      text-decoration: none;
      font-size: 18px;
      transition: color 0.3s;
      position: relative;
    }

    .nav-link:hover {
      color: var(--primary);
    }

    .nav-link.active {
      color: var(--primary);
    }

    .nav-link.active::after {
      content: '';
      position: absolute;
      bottom: -12px;
      left: 0;
      width: 100%;
      height: 2px;
      background: linear-gradient(90deg, var(--primary), var(--primary-dark));
      border-radius: 2px;
    }

    .user-menu {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .user-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--primary);
    }

    .notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background: var(--primary);
      color: white;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      font-size: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Main Container */
    .container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 20px;
      display: flex;
      gap: 30px;
    }

    /* Stories Section */
    .stories-container {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 20px;
      margin-bottom: 30px;
      border: 1px solid var(--card-border);
      overflow-x: auto;
      display: flex;
      gap: 20px;
    }

    .story {
      display: flex;
      flex-direction: column;
      align-items: center;
      cursor: pointer;
      min-width: 70px;
    }

    .story-avatar {
      width: 66px;
      height: 66px;
      border-radius: 50%;
      padding: 3px;
      background: linear-gradient(45deg, var(--primary), var(--secondary));
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 8px;
    }

    .story-avatar-inner {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      background: var(--dark);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .story-avatar-inner img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .story-username {
      font-size: 12px;
      max-width: 70px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .story-add {
      background: linear-gradient(45deg, var(--primary), var(--primary-dark));
    }

    .story-add .story-avatar-inner {
      background: transparent;
      color: white;
      font-size: 24px;
    }

    /* Feed */
    .feed {
      flex: 1;
    }

    /* Post */
    .post {
      background: var(--card-bg);
      border-radius: 16px;
      margin-bottom: 30px;
      border: 1px solid var(--card-border);
      overflow: hidden;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .post:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
    }

    .post-header {
      display: flex;
      align-items: center;
      padding: 16px;
    }

    .post-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 12px;
      border: 2px solid var(--primary);
    }

    .post-user-info {
      flex: 1;
    }

    .post-username {
      font-weight: 600;
      margin-bottom: 2px;
    }

    .post-time {
      font-size: 12px;
      color: var(--gray);
    }

    .post-options {
      color: var(--gray);
      cursor: pointer;
      padding: 5px;
      border-radius: 50%;
      transition: background 0.3s;
    }

    .post-options:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    .post-media {
      width: 100%;
      max-height: 600px;
      object-fit: cover;
    }

    .post-actions {
      display: flex;
      justify-content: space-between;
      padding: 12px 16px;
      font-size: 24px;
    }

    .post-actions-left {
      display: flex;
      gap: 16px;
    }

    .post-action {
      cursor: pointer;
      transition: transform 0.2s, color 0.2s;
    }

    .post-action:hover {
      transform: scale(1.1);
    }

    .like-btn.liked {
      color: var(--error);
    }

    .post-stats {
      padding: 0 16px 8px;
    }

    .post-likes {
      font-weight: 600;
      margin-bottom: 6px;
    }

    .post-caption {
      margin-bottom: 6px;
    }

    .post-caption-username {
      font-weight: 600;
      margin-right: 6px;
    }

    .post-comments-link {
      color: var(--gray);
      cursor: pointer;
      font-size: 14px;
      margin-bottom: 8px;
      display: inline-block;
    }

    .post-comments-link:hover {
      color: var(--light);
    }

    .post-time-full {
      font-size: 10px;
      color: var(--gray);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 12px;
    }

    .comment-form {
      display: flex;
      border-top: 1px solid var(--card-border);
      padding: 12px 16px;
    }

    .comment-input {
      flex: 1;
      background: transparent;
      border: none;
      color: var(--light);
      font-size: 14px;
      padding: 8px 0;
      outline: none;
    }

    .comment-input::placeholder {
      color: var(--gray);
    }

    .comment-submit {
      background: transparent;
      border: none;
      color: var(--primary);
      font-weight: 600;
      cursor: pointer;
      padding: 8px 0 8px 12px;
      transition: color 0.3s;
    }

    .comment-submit:hover {
      color: var(--primary-dark);
    }

    .comment-submit:disabled {
      color: var(--gray);
      cursor: not-allowed;
    }

    /* Sidebar */
    .sidebar {
      width: 320px;
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .sidebar-card {
      background: var(--card-bg);
      border-radius: 16px;
      padding: 20px;
      border: 1px solid var(--card-border);
    }

    .sidebar-title {
      font-size: 16px;
      font-weight: 600;
      margin-bottom: 16px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .sidebar-link {
      color: var(--primary);
      font-size: 12px;
      text-decoration: none;
      font-weight: 600;
    }

    .sidebar-link:hover {
      text-decoration: underline;
    }

    .suggested-user {
      display: flex;
      align-items: center;
      margin-bottom: 16px;
    }

    .suggested-user:last-child {
      margin-bottom: 0;
    }

    .suggested-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 12px;
    }

    .suggested-info {
      flex: 1;
    }

    .suggested-username {
      font-weight: 600;
      font-size: 14px;
    }

    .suggested-followers {
      font-size: 12px;
      color: var(--gray);
    }

    .follow-btn {
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 6px 12px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s;
    }

    .follow-btn:hover {
      background: var(--primary-dark);
    }

    .footer-links {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 20px;
    }

    .footer-link {
      font-size: 12px;
      color: var(--gray);
      text-decoration: none;
    }

    .footer-link:hover {
      text-decoration: underline;
    }

    .copyright {
      font-size: 12px;
      color: var(--gray);
      margin-top: 16px;
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.8);
      z-index: 1000;
      justify-content: center;
      align-items: center;
      backdrop-filter: blur(5px);
    }

    .modal-content {
      background: var(--card-bg);
      border-radius: 16px;
      width: 90%;
      max-width: 500px;
      max-height: 80vh;
      overflow: hidden;
      border: 1px solid var(--card-border);
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
    }

    .modal-header {
      padding: 16px 20px;
      border-bottom: 1px solid var(--card-border);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-title {
      font-weight: 600;
      font-size: 18px;
    }

    .modal-close {
      background: none;
      border: none;
      color: var(--light);
      font-size: 24px;
      cursor: pointer;
      padding: 0;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      transition: background 0.3s;
    }

    .modal-close:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    .modal-body {
      padding: 20px;
      max-height: 60vh;
      overflow-y: auto;
    }

    .comment {
      display: flex;
      margin-bottom: 16px;
    }

    .comment:last-child {
      margin-bottom: 0;
    }

    .comment-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 12px;
    }

    .comment-content {
      flex: 1;
    }

    .comment-header {
      display: flex;
      align-items: center;
      margin-bottom: 4px;
    }

    .comment-username {
      font-weight: 600;
      font-size: 14px;
      margin-right: 8px;
    }

    .comment-text {
      font-size: 14px;
      line-height: 1.4;
    }

    .comment-time {
      font-size: 12px;
      color: var(--gray);
      margin-top: 4px;
    }

    /* Responsive Design */
    @media (max-width: 992px) {
      .container {
        flex-direction: column;
      }
      
      .sidebar {
        width: 100%;
        order: -1;
      }
      
      .stories-container {
        padding: 16px;
      }
    }

    @media (max-width: 768px) {
      .navbar {
        padding: 10px 15px;
      }
      
      .nav-links {
        gap: 15px;
      }
      
      .container {
        padding: 15px;
      }
      
      .post-actions {
        font-size: 22px;
      }
    }

    @media (max-width: 576px) {
      .nav-links .nav-text {
        display: none;
      }
      
      .logo span {
        display: none;
      }
      
      .stories-container {
        padding: 12px;
        gap: 15px;
      }
      
      .story {
        min-width: 60px;
      }
      
      .story-avatar {
        width: 56px;
        height: 56px;
      }
    }

    /* Animation */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .post {
      animation: fadeIn 0.5s ease;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar">
    <div class="nav-container">
      <div class="logo">
        <i class="fas fa-camera-retro"></i>
        Anas <span>Insta</span>
      </div>
      
      <div class="nav-links">
        <a href="feed.php" class="nav-link active">
          <i class="fas fa-home"></i>
          <span class="nav-text">Home</span>
        </a>
        <a href="search.php" class="nav-link">
          <i class="fas fa-search"></i>
          <span class="nav-text">Search</span>
        </a>
        <a href="post.php" class="nav-link">
          <i class="fas fa-plus-square"></i>
          <span class="nav-text">Create</span>
        </a>
        <a href="message.php" class="nav-link" style="position: relative;">
          <i class="fas fa-paper-plane"></i>
          <span class="nav-text">Messages</span>
          <span id="unread-count" class="notification-badge">0</span>
        </a>
      </div>
      
      <div class="user-menu">
        <a href="profile.php?id=<?php echo $currentUser['id']; ?>">
          <img src="<?php echo htmlspecialchars($currentUser['profile_pic'] ?: 'uploads/default.png'); ?>" class="user-avatar" alt="Profile">
        </a>
        <a href="logout.php" class="nav-link">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </div>
    </div>
  </nav>

  <!-- Main Container -->
  <div class="container">
    <!-- Feed -->
    <div class="feed">
      <!-- Stories -->
      <div class="stories-container">
        <div class="story">
          <div class="story-avatar story-add">
            <div class="story-avatar-inner">
              <i class="fas fa-plus"></i>
            </div>
          </div>
          <div class="story-username">Your Story</div>
        </div>
        
        <!-- Sample Stories -->
        <div class="story">
          <div class="story-avatar">
            <div class="story-avatar-inner">
              <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="User">
            </div>
          </div>
          <div class="story-username">jessica</div>
        </div>
        
        <div class="story">
          <div class="story-avatar">
            <div class="story-avatar-inner">
              <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="User">
            </div>
          </div>
          <div class="story-username">michael</div>
        </div>
        
        <div class="story">
          <div class="story-avatar">
            <div class="story-avatar-inner">
              <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="User">
            </div>
          </div>
          <div class="story-username">sarah</div>
        </div>
        
        <div class="story">
          <div class="story-avatar">
            <div class="story-avatar-inner">
              <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" alt="User">
            </div>
          </div>
          <div class="story-username">david</div>
        </div>
      </div>

      <!-- Posts -->
      <?php while ($post = $postsResult->fetch_assoc()): 
          $pId = $post['id'];
          // likes count
          $lc = $conn->query("SELECT COUNT(*) c FROM likes WHERE post_id=$pId")->fetch_assoc()['c'];
          $isLiked = $conn->query("SELECT id FROM likes WHERE post_id=$pId AND user_id=$user_id")->num_rows > 0;
          // comments count
          $cc = $conn->query("SELECT COUNT(*) c FROM comments WHERE post_id=$pId")->fetch_assoc()['c'];
      ?>
        <div class="post" data-id="<?php echo $pId; ?>">
          <div class="post-header">
            <img src="<?php echo htmlspecialchars($post['profile_pic'] ?: 'uploads/default.png'); ?>" class="post-avatar" alt="User Avatar">
            <div class="post-user-info">
              <div class="post-username"><?php echo htmlspecialchars($post['username']); ?></div>
              <div class="post-time">2 hours ago</div>
            </div>
            <div class="post-options">
              <i class="fas fa-ellipsis-h"></i>
            </div>
          </div>
          
          <?php if ($post['media_url']): 
              $ext = pathinfo($post['media_url'], PATHINFO_EXTENSION);
              if (in_array($ext,['jpg','jpeg','png','gif'])): ?>
                <img class="post-media" src="<?php echo $post['media_url']; ?>" alt="Post Image">
              <?php else: ?>
                <video class="post-media" controls>
                  <source src="<?php echo $post['media_url']; ?>" type="video/mp4">
                  Your browser does not support the video tag.
                </video>
              <?php endif; endif; ?>
              
          <div class="post-actions">
            <div class="post-actions-left">
              <i class="post-action like-btn <?php echo $isLiked ? 'fas fa-heart liked' : 'far fa-heart'; ?>" data-liked="<?php echo $isLiked ? 'true' : 'false'; ?>"></i>
              <i class="post-action far fa-comment comment-btn"></i>
              <i class="post-action far fa-paper-plane"></i>
            </div>
            <div class="post-actions-right">
              <i class="post-action far fa-bookmark"></i>
            </div>
          </div>
          
          <div class="post-stats">
            <div class="post-likes"><?php echo $lc; ?> likes</div>
            <?php if ($post['content']): ?>
              <div class="post-caption">
                <span class="post-caption-username"><?php echo htmlspecialchars($post['username']); ?></span>
                <?php echo htmlspecialchars($post['content']); ?>
              </div>
            <?php endif; ?>
            <div class="post-comments-link" onclick="openComments(<?php echo $pId; ?>)">View all <?php echo $cc; ?> comments</div>
            <div class="post-time-full">2 HOURS AGO</div>
          </div>
          
          <form class="comment-form" method="POST" action="comment.php">
            <input type="hidden" name="post_id" value="<?php echo $pId; ?>">
            <input type="text" name="text" class="comment-input" placeholder="Add a comment..." required>
            <button type="submit" class="comment-submit">Post</button>
          </form>
        </div>
      <?php endwhile; ?>
    </div>
    
    <!-- Sidebar -->
    <div class="sidebar">
      <!-- User Profile -->
      <div class="sidebar-card">
        <div class="suggested-user">
          <img src="<?php echo htmlspecialchars($currentUser['profile_pic'] ?: 'uploads/default.png'); ?>" class="suggested-avatar" alt="Your Avatar">
          <div class="suggested-info">
            <div class="suggested-username"><?php echo htmlspecialchars($currentUser['username']); ?></div>
            <div class="suggested-followers">Your Profile</div>
          </div>
          <a href="profile.php?id=<?php echo $currentUser['id']; ?>" class="follow-btn">View</a>
        </div>
      </div>
      
      <!-- Suggested Users -->
      <div class="sidebar-card">
        <div class="sidebar-title">
          <span>Suggested for you</span>
          <a href="#" class="sidebar-link">See All</a>
        </div>
        
        <div class="suggested-user">
          <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" class="suggested-avatar" alt="User">
          <div class="suggested-info">
            <div class="suggested-username">alex_johnson</div>
            <div class="suggested-followers">Followed by jessica + 2 more</div>
          </div>
          <button class="follow-btn">Follow</button>
        </div>
        
        <div class="suggested-user">
          <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" class="suggested-avatar" alt="User">
          <div class="suggested-info">
            <div class="suggested-username">emma_wilson</div>
            <div class="suggested-followers">Followed by michael + 5 more</div>
          </div>
          <button class="follow-btn">Follow</button>
        </div>
        
        <div class="suggested-user">
          <img src="https://images.unsplash.com/photo-1527980965255-d3b416303d12?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=100&q=80" class="suggested-avatar" alt="User">
          <div class="suggested-info">
            <div class="suggested-username">robert_davis</div>
            <div class="suggested-followers">New to Anas Insta</div>
          </div>
          <button class="follow-btn">Follow</button>
        </div>
      </div>
      
      <!-- Footer Links -->
      <!-- <div class="sidebar-card"> -->
        <!-- <div class="footer-links"> -->
          <!-- <a href="#" class="footer-link">About</a> -->
          <!-- <a href="#" class="footer-link">Help</a> -->
          <!-- <a href="#" class="footer-link">API</a> -->
          <!-- <a href="#" class="footer-link">Privacy</a> -->
          <!-- <a href="#" class="footer-link">Terms</a> -->
          <!-- <a href="#" class="footer-link">Locations</a> -->
          <!-- <a href="#" class="footer-link">Language</a> -->
        <!-- </div> -->
        <!-- <div class="copyright">© 2023 ANAS INSTA</div> -->
      <!-- </div> -->
    <!-- </div> -->
  <!-- </div> -->

  <!-- Comment Modal -->
  <div class="modal" id="commentModal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">Comments</div>
        <button class="modal-close">&times;</button>
      </div>
      <div class="modal-body" id="commentsArea">
        <!-- Comments will be loaded here -->
      </div>
    </div>
  </div>

  <script>
    // Like functionality
    document.querySelectorAll('.like-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const post = this.closest('.post');
        const postId = post.dataset.id;
        const isLiked = this.dataset.liked === 'true';
        
        fetch('like.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'post_id=' + postId
        })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            // Toggle like button appearance
            if (data.action === 'liked') {
              this.classList.remove('far');
              this.classList.add('fas', 'liked');
              this.dataset.liked = 'true';
            } else {
              this.classList.remove('fas', 'liked');
              this.classList.add('far');
              this.dataset.liked = 'false';
            }
            
            // Update likes count
            post.querySelector('.post-likes').textContent = data.likes_count + ' likes';
          }
        })
        .catch(error => console.error('Error:', error));
      });
    });

    // Comment modal
    const modal = document.getElementById('commentModal');
    const closeModal = document.querySelector('.modal-close');
    
    function openComments(postId) {
      modal.style.display = 'flex';
      
      // Load comments via AJAX
      fetch('get_comments.php?post_id=' + postId)
        .then(r => r.text())
        .then(html => {
          document.getElementById('commentsArea').innerHTML = html;
        })
        .catch(error => {
          document.getElementById('commentsArea').innerHTML = '<p>Error loading comments</p>';
        });
    }
    
    // Close modal when clicking X or outside
    closeModal.addEventListener('click', () => {
      modal.style.display = 'none';
    });
    
    window.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.style.display = 'none';
      }
    });

    // Message notification badge
    function updateUnreadCount() {
      fetch('check_messages.php')
        .then(res => res.json())
        .then(messages => {
          const badge = document.getElementById('unread-count');
          if (badge) {
            badge.textContent = messages.length;
            
            // Show notifications for new messages
            messages.forEach(msg => {
              if (Notification.permission === 'granted') {
                new Notification(`New message from ${msg.username}`, {
                  body: msg.message,
                  icon: 'icon.png'
                });
              }
              
              // Mark as read
              fetch('mark_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message_id: msg.id })
              });
            });
          }
        })
        .catch(error => console.error('Error fetching messages:', error));
    }
    
    // Request notification permission on page load
    if (Notification.permission !== 'granted') {
      Notification.requestPermission();
    }
    
    // Update unread count every 5 seconds
    updateUnreadCount();
    setInterval(updateUnreadCount, 5000);
  </script>
</body>
</html>