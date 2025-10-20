<?php
// profile.php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$currentUserId = $_SESSION['user_id'];

$targetId = isset($_GET['id']) ? intval($_GET['id']) : $currentUserId;

// fetch target user
$stmt = $conn->prepare("SELECT id, username, profile_pic, bio, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $targetId);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows == 0) {
    echo "User not found"; exit;
}
$target = $res->fetch_assoc();
$stmt->close();

// is following?
$stmt = $conn->prepare("SELECT id FROM followers WHERE follower_id = ? AND following_id = ?");
$stmt->bind_param("ii", $currentUserId, $targetId);
$stmt->execute(); $stmt->store_result();
$isFollowing = $stmt->num_rows > 0; $stmt->close();

// follower counts
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM followers WHERE following_id = ?");
$stmt->bind_param("i",$targetId); $stmt->execute(); $fcount = $stmt->get_result()->fetch_assoc()['cnt']; $stmt->close();
$stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM followers WHERE follower_id = ?");
$stmt->bind_param("i",$targetId); $stmt->execute(); $followingCount = $stmt->get_result()->fetch_assoc()['cnt']; $stmt->close();

// posts by target
$stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $targetId); $stmt->execute();
$postsRes = $stmt->get_result();
$postCount = $postsRes->num_rows;
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($target['username']); ?> - Profile | Anas Insta</title>
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
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--darker), var(--dark));
            color: var(--light);
            min-height: 100vh;
        }

        /* Navigation */
        .navbar {
            background: var(--card-bg);
            border-bottom: 1px solid var(--card-border);
            padding: 15px 25px;
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--light);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo i {
            color: var(--primary);
        }

        .logo span {
            color: var(--primary);
        }

        .nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .nav-link {
            color: var(--light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .nav-link:hover {
            color: var(--primary);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        /* Back Button */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--light);
            text-decoration: none;
            margin-bottom: 25px;
            padding: 10px 16px;
            border-radius: 10px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: var(--primary);
            transform: translateX(-3px);
        }

        /* Profile Header */
        .profile-header {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 40px;
            border: 1px solid var(--card-border);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .profile-content {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .avatar-container {
            position: relative;
        }

        .avatar-big {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid transparent;
            background: linear-gradient(135deg, var(--primary), var(--secondary)) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }

        .profile-info {
            flex: 1;
        }

        .profile-username {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--light), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .profile-bio {
            font-size: 1.1rem;
            line-height: 1.6;
            color: var(--gray);
            margin-bottom: 20px;
            max-width: 500px;
        }

        .profile-stats {
            display: flex;
            gap: 30px;
            margin-bottom: 25px;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--light);
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--gray);
        }

        .profile-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: var(--light);
            border: 1px solid var(--card-border);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--primary);
        }

        .btn-danger {
            background: linear-gradient(135deg, #EF4444, #DC2626);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }

        /* Posts Section */
        .posts-section {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--card-border);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--card-border);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--light);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--primary);
        }

        .posts-count {
            color: var(--gray);
            font-size: 1rem;
        }

        /* Post Grid */
        .post-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .post-item {
            background: var(--darker);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid var(--card-border);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .post-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            border-color: var(--primary);
        }

        .post-media {
            width: 100%;
            height: 250px;
            object-fit: cover;
            display: block;
        }

        .post-video {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: var(--dark);
        }

        .post-content {
            padding: 15px;
        }

        .post-text {
            color: var(--light);
            font-size: 0.9rem;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .post-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .post-item:hover .post-overlay {
            opacity: 1;
        }

        .post-actions {
            display: flex;
            gap: 15px;
        }

        .post-action {
            color: white;
            font-size: 1.2rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--gray-dark);
        }

        .empty-title {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: var(--light);
        }

        .empty-description {
            margin-bottom: 25px;
            line-height: 1.6;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-content {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }

            .avatar-big {
                width: 120px;
                height: 120px;
            }

            .profile-stats {
                justify-content: center;
            }

            .profile-actions {
                justify-content: center;
            }

            .post-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }

            .container {
                padding: 20px 15px;
            }

            .profile-header {
                padding: 30px 20px;
            }

            .posts-section {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .profile-username {
                font-size: 1.8rem;
            }

            .profile-stats {
                gap: 20px;
            }

            .post-grid {
                grid-template-columns: 1fr;
            }

            .nav-links {
                gap: 15px;
            }

            .nav-link span {
                display: none;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-header, .posts-section {
            animation: fadeInUp 0.6s ease;
        }

        .post-item {
            animation: fadeInUp 0.4s ease;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="feed.php" class="logo">
                <i class="fas fa-camera-retro"></i>
                Anas <span>Insta</span>
            </a>
            <div class="nav-links">
                <a href="feed.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </a>
                <a href="search.php" class="nav-link">
                    <i class="fas fa-search"></i>
                    <span>Search</span>
                </a>
                <a href="post.php" class="nav-link">
                    <i class="fas fa-plus-square"></i>
                    <span>Create</span>
                </a>
                <a href="message.php" class="nav-link">
                    <i class="fas fa-paper-plane"></i>
                    <span>Messages</span>
                </a>
                <a href="profile.php" class="nav-link">
                    <?php 
                    $user_stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
                    $user_stmt->bind_param("i", $currentUserId);
                    $user_stmt->execute();
                    $user_result = $user_stmt->get_result();
                    $user_data = $user_result->fetch_assoc();
                    ?>
                    <img src="<?php echo htmlspecialchars($user_data['profile_pic'] ?: 'uploads/default.png'); ?>" class="user-avatar" alt="Profile">
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container">
        <!-- Back Button -->
        <a href="feed.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Feed
        </a>

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-content">
                <div class="avatar-container">
                    <img src="<?php echo htmlspecialchars($target['profile_pic'] ?: 'uploads/default.png'); ?>" class="avatar-big" alt="<?php echo htmlspecialchars($target['username']); ?>">
                </div>
                <div class="profile-info">
                    <h1 class="profile-username"><?php echo htmlspecialchars($target['username']); ?></h1>
                    
                    <?php if (!empty($target['bio'])): ?>
                        <p class="profile-bio"><?php echo nl2br(htmlspecialchars($target['bio'])); ?></p>
                    <?php endif; ?>

                    <div class="profile-stats">
                        <div class="stat">
                            <span class="stat-number"><?php echo intval($postCount); ?></span>
                            <span class="stat-label">Posts</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number"><?php echo intval($fcount); ?></span>
                            <span class="stat-label">Followers</span>
                        </div>
                        <div class="stat">
                            <span class="stat-number"><?php echo intval($followingCount); ?></span>
                            <span class="stat-label">Following</span>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <?php if ($currentUserId !== $targetId): ?>
                            <?php if ($isFollowing): ?>
                                <form method="POST" action="unfollow.php" style="display:inline;">
                                    <input type="hidden" name="following_id" value="<?php echo $targetId; ?>">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-user-minus"></i>
                                        Unfollow
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST" action="follow.php" style="display:inline;">
                                    <input type="hidden" name="following_id" value="<?php echo $targetId; ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-user-plus"></i>
                                        Follow
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="chat.php?user=<?php echo $targetId; ?>" class="btn btn-secondary">
                                <i class="fas fa-comment"></i>
                                Message
                            </a>
                        <?php else: ?>
                            <a href="post.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                New Post
                            </a>
                            <a href="edit_profile.php" class="btn btn-secondary">
                                <i class="fas fa-edit"></i>
                                Edit Profile
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Posts Section -->
        <div class="posts-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-images"></i>
                    Posts
                </h2>
                <span class="posts-count"><?php echo intval($postCount); ?> posts</span>
            </div>

            <?php if ($postCount > 0): ?>
                <div class="post-grid">
                    <?php while ($p = $postsRes->fetch_assoc()): ?>
                        <div class="post-item">
                            <?php if (!empty($p['media_url'])): 
                                $ext = strtolower(pathinfo($p['media_url'], PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg','jpeg','png','gif'])): ?>
                                    <img src="<?php echo htmlspecialchars($p['media_url']); ?>" class="post-media" alt="Post image">
                                <?php else: ?>
                                    <video class="post-video" controls>
                                        <source src="<?php echo htmlspecialchars($p['media_url']); ?>">
                                    </video>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="post-content">
                                    <p class="post-text"><?php echo nl2br(htmlspecialchars($p['content'])); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="post-overlay">
                                <div class="post-actions">
                                    <i class="fas fa-heart post-action"></i>
                                    <i class="fas fa-comment post-action"></i>
                                    <i class="fas fa-share post-action"></i>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-camera"></i>
                    </div>
                    <h3 class="empty-title">No Posts Yet</h3>
                    <p class="empty-description">
                        <?php echo $currentUserId === $targetId ? 'Share your first post to get started!' : 'This user hasn\'t posted anything yet.'; ?>
                    </p>
                    <?php if ($currentUserId === $targetId): ?>
                        <a href="post.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Create Your First Post
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add hover effects to post items
        document.querySelectorAll('.post-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Add click functionality to post items (you can expand this)
        document.querySelectorAll('.post-item').forEach(item => {
            item.addEventListener('click', function() {
                // Add post view functionality here
                console.log('Post clicked - implement post view modal');
            });
        });
    </script>
</body>
</html>