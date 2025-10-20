<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'config.php';

$current_user = $_SESSION['user_id'];

// Get distinct users whom current user has chatted with
$sql = "SELECT u.id, u.username, u.profile_pic, MAX(m.created_at) as last_msg_time 
        FROM messages m
        JOIN users u ON (CASE 
                            WHEN m.sender_id = ? THEN m.receiver_id = u.id
                            WHEN m.receiver_id = ? THEN m.sender_id = u.id
                         END)
        WHERE m.sender_id = ? OR m.receiver_id = ?
        GROUP BY u.id, u.username, u.profile_pic
        ORDER BY last_msg_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $current_user, $current_user, $current_user, $current_user);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messages - Anas Insta</title>
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
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header Styles */
        .header {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 25px 30px;
            margin-bottom: 30px;
            border: 1px solid var(--card-border);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--light), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-title i {
            font-size: 2.2rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .back-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
        }

        .back-btn i {
            font-size: 1.1rem;
        }

        /* Messages Container */
        .messages-container {
            background: var(--card-bg);
            border-radius: 20px;
            border: 1px solid var(--card-border);
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        /* User List Styles */
        .user {
            padding: 20px 25px;
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .user::before {
            content: '';
            position: absolute;
            left: -100%;
            top: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(139, 92, 246, 0.1), transparent);
            transition: left 0.6s ease;
        }

        .user:hover::before {
            left: 100%;
        }

        .user:hover {
            background: rgba(139, 92, 246, 0.05);
            transform: translateX(5px);
        }

        .user:last-child {
            border-bottom: none;
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
            border: 3px solid transparent;
            background: linear-gradient(135deg, var(--primary), var(--secondary)) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            transition: transform 0.3s ease;
        }

        .user:hover .user-avatar {
            transform: scale(1.1);
        }

        .user-details {
            flex: 1;
        }

        .username {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--light);
            text-decoration: none;
            display: block;
        }

        .username:hover {
            color: var(--primary);
        }

        .last-active {
            font-size: 0.9rem;
            color: var(--gray);
        }

        .actions {
            display: flex;
            gap: 12px;
        }

        .chat-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }

        .chat-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(139, 92, 246, 0.4);
        }

        .chat-btn i {
            font-size: 0.9rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 30px;
            color: var(--gray);
        }

        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0.7;
        }

        .empty-title {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--light);
        }

        .empty-description {
            margin-bottom: 25px;
            font-size: 1rem;
            line-height: 1.6;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .search-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }

        .search-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
        }

        /* Notification Badge */
        .notification-badge {
            position: fixed;
            top: 30px;
            right: 30px;
            background: linear-gradient(135deg, #EF4444, #DC2626);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .notification-badge:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.5);
        }

        /* Animations */
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

        .user {
            animation: fadeInUp 0.5s ease;
        }

        .user:nth-child(1) { animation-delay: 0.1s; }
        .user:nth-child(2) { animation-delay: 0.2s; }
        .user:nth-child(3) { animation-delay: 0.3s; }
        .user:nth-child(4) { animation-delay: 0.4s; }
        .user:nth-child(5) { animation-delay: 0.5s; }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header {
                padding: 20px;
                border-radius: 16px;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .user {
                padding: 15px 20px;
            }
            
            .user-avatar {
                width: 50px;
                height: 50px;
            }
            
            .chat-btn {
                padding: 8px 16px;
                font-size: 0.9rem;
            }
            
            .notification-badge {
                top: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
            }
        }

        @media (max-width: 480px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .user-info {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .actions {
                width: 100%;
                justify-content: center;
            }
            
            .chat-btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Custom Scrollbar */
        .messages-container {
            max-height: 70vh;
            overflow-y: auto;
        }

        .messages-container::-webkit-scrollbar {
            width: 8px;
        }

        .messages-container::-webkit-scrollbar-track {
            background: var(--dark);
        }

        .messages-container::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 4px;
        }

        .messages-container::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--secondary));
        }
    </style>
</head>
<body>
    <!-- Notification Badge -->
    <div class="notification-badge" id="unread-count">
        <i class="fas fa-comment"></i>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <h1 class="page-title">
                    <i class="fas fa-paper-plane"></i>
                    Messages
                </h1>
                <a href="feed.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Feed
                </a>
            </div>
        </div>

        <!-- Messages Container -->
        <div class="messages-container">
            <?php if($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="user">
                        <div class="user-info">
                            <img src="<?php echo htmlspecialchars($row['profile_pic']); ?>" class="user-avatar" alt="<?php echo htmlspecialchars($row['username']); ?>">
                            <div class="user-details">
                                <a href="chat.php?user=<?php echo $row['id']; ?>" class="username">
                                    <?php echo htmlspecialchars($row['username']); ?>
                                </a>
                                <div class="last-active">
                                    <?php 
                                    // Format the last message time
                                    if ($row['last_msg_time']) {
                                        $time_ago = time() - strtotime($row['last_msg_time']);
                                        if ($time_ago < 60) echo 'Active now';
                                        elseif ($time_ago < 3600) echo floor($time_ago/60) . ' minutes ago';
                                        elseif ($time_ago < 86400) echo floor($time_ago/3600) . ' hours ago';
                                        else echo date('M j', strtotime($row['last_msg_time']));
                                    } else {
                                        echo 'No messages yet';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="actions">
                            <a href="chat.php?user=<?php echo $row['id']; ?>" class="chat-btn">
                                <i class="fas fa-comment"></i>
                                Chat
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h2 class="empty-title">No conversations yet</h2>
                    <p class="empty-description">
                        Start connecting with other users by sending them messages. Find people to chat with through the search feature.
                    </p>
                    <a href="search.php" class="search-link">
                        <i class="fas fa-search"></i>
                        Find People to Message
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        window.onload = function() {
            const unreadBadge = document.getElementById('unread-count');

            // Request notification permission
            if(Notification.permission !== "granted") {
                Notification.requestPermission();
            }

            function fetchUnread() {
                fetch('check_messages.php')
                .then(res => res.json())
                .then(messages => {
                    // Update badge with count
                    if (messages.length > 0) {
                        unreadBadge.innerHTML = `<i class="fas fa-comment"></i> ${messages.length}`;
                        unreadBadge.style.display = 'flex';
                    } else {
                        unreadBadge.style.display = 'none';
                    }

                    // Show notifications for new messages
                    messages.forEach(msg => {
                        if(Notification.permission === "granted"){
                            new Notification(`New message from ${msg.username}`, {
                                body: msg.message,
                                icon: 'icon.png'
                            });
                        }

                        // Mark as read after showing notification
                        fetch('mark_read.php',{
                            method:'POST',
                            headers: {'Content-Type':'application/json'},
                            body: JSON.stringify({ message_id: msg.id })
                        });
                    });
                })
                .catch(error => {
                    console.error('Error fetching messages:', error);
                });
            }

            // Initial check
            fetchUnread();
            
            // Poll every 3 seconds
            setInterval(fetchUnread, 3000);

            // Add hover effects to user cards
            document.querySelectorAll('.user').forEach(user => {
                user.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(5px)';
                });
                
                user.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });
        };
    </script>
</body>
</html>