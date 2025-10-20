<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$searchResults = [];

// Fetch users (search query optional)
if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $query = trim($_GET['query']);
    $stmt = $conn->prepare("SELECT id, username, profile_pic FROM users WHERE username LIKE ? AND id != ?");
    $likeQuery = "%$query%";
    $stmt->bind_param("si", $likeQuery, $user_id);
} else {
    // If no search, fetch all users except current
    $stmt = $conn->prepare("SELECT id, username, profile_pic FROM users WHERE id != ?");
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$searchResults = $result->fetch_all(MYSQLI_ASSOC);

// Check if already following
function isFollowing($conn, $follower_id, $following_id) {
    $stmt = $conn->prepare("SELECT id FROM followers WHERE follower_id = ? AND following_id = ?");
    $stmt->bind_param("ii", $follower_id, $following_id);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Users - Anas Insta</title>
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

        .nav-link.active {
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

        /* Search Header */
        .search-header {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--card-border);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .search-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .search-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--light), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .search-title i {
            font-size: 1.8rem;
        }

        /* Search Form */
        .search-form {
            display: flex;
            gap: 12px;
            max-width: 500px;
        }

        .search-input {
            flex: 1;
            background: var(--darker);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 14px 20px;
            color: var(--light);
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }

        .search-input::placeholder {
            color: var(--gray);
        }

        .search-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            color: white;
            padding: 14px 24px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
        }

        /* Results Section */
        .results-section {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 30px;
            border: 1px solid var(--card-border);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--card-border);
        }

        .results-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--light);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .results-count {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* User Cards */
        .user-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background: var(--darker);
            border: 1px solid var(--card-border);
            border-radius: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            animation: fadeInUp 0.4s ease;
        }

        .user-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            border-color: var(--primary);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            flex: 1;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid transparent;
            background: linear-gradient(135deg, var(--primary), var(--secondary)) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }

        .user-details {
            flex: 1;
        }

        .username {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--light);
            margin-bottom: 4px;
        }

        .user-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 18px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(139, 92, 246, 0.4);
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
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
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

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }

            .search-header {
                padding: 20px;
            }

            .results-section {
                padding: 20px;
            }

            .search-form {
                flex-direction: column;
            }

            .user-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .user-actions {
                width: 100%;
                justify-content: flex-end;
            }

            .nav-links {
                gap: 15px;
            }

            .nav-link span {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .user-actions {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .search-title {
                font-size: 1.6rem;
            }

            .user-avatar {
                width: 50px;
                height: 50px;
            }

            .username {
                font-size: 1.1rem;
            }
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
                <a href="search.php" class="nav-link active">
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
                    $user_stmt->bind_param("i", $user_id);
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

        <!-- Search Header -->
        <div class="search-header">
            <h1 class="search-title">
                <i class="fas fa-search"></i>
                Search Users
            </h1>
            <form method="GET" action="" class="search-form">
                <input type="text" name="query" class="search-input" placeholder="Search by username..." value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                    Search
                </button>
            </form>
        </div>

        <!-- Results Section -->
        <div class="results-section">
            <div class="results-header">
                <h2 class="results-title">
                    <i class="fas fa-users"></i>
                    Search Results
                </h2>
                <span class="results-count"><?php echo count($searchResults); ?> users found</span>
            </div>

            <?php if (!empty($searchResults)): ?>
                <?php foreach ($searchResults as $user): ?>
                    <div class="user-card">
                        <div class="user-info">
                            <img src="<?php echo htmlspecialchars($user['profile_pic'] ?: 'uploads/default.png'); ?>" class="user-avatar" alt="<?php echo htmlspecialchars($user['username']); ?>">
                            <div class="user-details">
                                <div class="username"><?php echo htmlspecialchars($user['username']); ?></div>
                            </div>
                        </div>
                        <div class="user-actions">
                            <?php if (isFollowing($conn, $user_id, $user['id'])): ?>
                                <a href="unfollow.php?id=<?php echo $user['id']; ?>" class="btn btn-danger">
                                    <i class="fas fa-user-minus"></i>
                                    Unfollow
                                </a>
                            <?php else: ?>
                                <a href="follow.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-user-plus"></i>
                                    Follow
                                </a>
                            <?php endif; ?>
                            <a href="chat.php?user=<?php echo $user['id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-comment"></i>
                                Message
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <h3 class="empty-title">No Users Found</h3>
                    <p class="empty-description">
                        <?php echo isset($_GET['query']) ? 
                            'No users found matching your search. Try different keywords.' : 
                            'No other users found. Be the first to invite friends!'; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Add hover effects to user cards
        document.querySelectorAll('.user-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Auto-focus search input
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.focus();
            }
        });

        // Add loading state to search button
        document.querySelector('.search-form').addEventListener('submit', function() {
            const button = this.querySelector('.search-btn');
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
            button.disabled = true;
        });
    </script>
</body>
</html>