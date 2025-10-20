<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get current user info for header
$user_id = $_SESSION['user_id'];
$user_stmt = $conn->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();
$user_name = $user_data['username'];
$user_avatar = $user_data['profile_pic'] ?: 'uploads/default.png';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = $_POST['content'];
    $media_url = "";

    // File upload (image/video)
    if (!empty($_FILES['media']['name'])) {
        $targetDir = "uploads/posts/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName = time() . "_" . basename($_FILES['media']['name']);
        $targetFile = $targetDir . $fileName;
        move_uploaded_file($_FILES['media']['tmp_name'], $targetFile);
        $media_url = $targetFile;
    }

    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, media_url) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $content, $media_url);

    if ($stmt->execute()) {
        header("Location: feed.php");
        exit();
    } else {
        $error = "Post failed: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - Anas Insta</title>
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
            max-width: 800px;
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

        /* Create Post Section */
        .create-post-section {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 0;
            border: 1px solid var(--card-border);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .post-header {
            background: var(--card-bg);
            border-bottom: 1px solid var(--card-border);
            padding: 25px 30px;
            position: relative;
        }

        .post-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .post-title {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--light), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .post-title i {
            font-size: 1.6rem;
        }

        /* Post Form */
        .post-form {
            padding: 30px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .post-user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid transparent;
            background: linear-gradient(135deg, var(--primary), var(--secondary)) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }

        .post-username {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--light);
        }

        /* Content Area */
        .content-area {
            margin-bottom: 25px;
        }

        .content-label {
            display: block;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--light);
            font-size: 1rem;
        }

        .content-textarea {
            width: 100%;
            background: var(--darker);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 16px 20px;
            color: var(--light);
            font-size: 1rem;
            line-height: 1.5;
            resize: vertical;
            min-height: 120px;
            outline: none;
            transition: all 0.3s ease;
        }

        .content-textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }

        .content-textarea::placeholder {
            color: var(--gray);
        }

        .char-count {
            text-align: right;
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 5px;
        }

        /* Media Upload */
        .media-upload {
            margin-bottom: 30px;
        }

        .upload-label {
            display: block;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--light);
            font-size: 1rem;
        }

        .upload-area {
            border: 2px dashed var(--card-border);
            border-radius: 12px;
            padding: 40px 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: var(--darker);
        }

        .upload-area:hover {
            border-color: var(--primary);
            background: rgba(139, 92, 246, 0.05);
        }

        .upload-area.dragover {
            border-color: var(--primary);
            background: rgba(139, 92, 246, 0.1);
        }

        .upload-icon {
            font-size: 3rem;
            color: var(--gray);
            margin-bottom: 15px;
        }

        .upload-text {
            color: var(--gray);
            margin-bottom: 10px;
        }

        .upload-hint {
            font-size: 0.8rem;
            color: var(--gray-dark);
        }

        .file-input {
            display: none;
        }

        .file-preview {
            margin-top: 20px;
            text-align: center;
        }

        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 12px;
            border: 1px solid var(--card-border);
        }

        .preview-video {
            max-width: 100%;
            max-height: 300px;
            border-radius: 12px;
            border: 1px solid var(--card-border);
        }

        .remove-preview {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .remove-preview:hover {
            background: var(--primary-dark);
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            align-items: center;
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

        /* Error Message */
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #EF4444;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }

            .post-header {
                padding: 20px;
            }

            .post-form {
                padding: 20px;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .nav-links {
                gap: 15px;
            }

            .nav-link span {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .user-info {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }

            .post-title {
                font-size: 1.5rem;
            }

            .upload-area {
                padding: 30px 15px;
            }

            .upload-icon {
                font-size: 2.5rem;
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

        .create-post-section {
            animation: fadeInUp 0.6s ease;
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
                    <img src="<?php echo htmlspecialchars($user_avatar); ?>" class="user-avatar" alt="Profile">
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

        <!-- Create Post Section -->
        <div class="create-post-section">
            <!-- Post Header -->
            <div class="post-header">
                <h1 class="post-title">
                    <i class="fas fa-plus-circle"></i>
                    Create Post
                </h1>
            </div>

            <!-- Post Form -->
            <form method="POST" enctype="multipart/form-data" class="post-form">
                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- User Info -->
                <div class="user-info">
                    <img src="<?php echo htmlspecialchars($user_avatar); ?>" class="post-user-avatar" alt="<?php echo htmlspecialchars($user_name); ?>">
                    <div class="post-username"><?php echo htmlspecialchars($user_name); ?></div>
                </div>

                <!-- Content Area -->
                <div class="content-area">
                    <label class="content-label" for="content">
                        <i class="fas fa-edit"></i>
                        What's on your mind?
                    </label>
                    <textarea name="content" id="content" class="content-textarea" placeholder="Share your thoughts, ideas, or experiences..." rows="4"></textarea>
                    <div class="char-count" id="charCount">0/500</div>
                </div>

                <!-- Media Upload -->
                <div class="media-upload">
                    <label class="upload-label">
                        <i class="fas fa-image"></i>
                        Add Media (Optional)
                    </label>
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="upload-text">Click to upload or drag and drop</div>
                        <div class="upload-hint">Supports images and videos (Max 10MB)</div>
                        <input type="file" name="media" id="media" class="file-input" accept="image/*,video/*">
                    </div>
                    <div class="file-preview" id="filePreview"></div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="feed.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                        Create Post
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Character counter
        const contentTextarea = document.getElementById('content');
        const charCount = document.getElementById('charCount');
        const maxChars = 500;

        contentTextarea.addEventListener('input', function() {
            const currentLength = this.value.length;
            charCount.textContent = `${currentLength}/${maxChars}`;
            
            if (currentLength > maxChars) {
                charCount.style.color = '#EF4444';
            } else {
                charCount.style.color = 'var(--gray)';
            }
        });

        // File upload handling
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('media');
        const filePreview = document.getElementById('filePreview');

        uploadArea.addEventListener('click', () => fileInput.click());

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFileSelect(e.dataTransfer.files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length) {
                handleFileSelect(e.target.files[0]);
            }
        });

        function handleFileSelect(file) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                let previewHTML = '';
                
                if (file.type.startsWith('image/')) {
                    previewHTML = `
                        <img src="${e.target.result}" class="preview-image" alt="Preview">
                    `;
                } else if (file.type.startsWith('video/')) {
                    previewHTML = `
                        <video controls class="preview-video">
                            <source src="${e.target.result}" type="${file.type}">
                            Your browser does not support the video tag.
                        </video>
                    `;
                }
                
                previewHTML += `
                    <br>
                    <button type="button" class="remove-preview" onclick="removePreview()">
                        <i class="fas fa-times"></i>
                        Remove File
                    </button>
                `;
                
                filePreview.innerHTML = previewHTML;
            };
            
            reader.readAsDataURL(file);
        }

        function removePreview() {
            fileInput.value = '';
            filePreview.innerHTML = '';
        }

        // Auto-focus content textarea
        document.addEventListener('DOMContentLoaded', function() {
            contentTextarea.focus();
        });

        // Add some interactive effects
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>