<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username   = $_POST['username'];
    $email      = $_POST['email'];
    $phone      = $_POST['phone'];
    $password   = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // File Upload
    $profile_pic = "uploads/default.png";
    if (!empty($_FILES['profile_pic']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir);
        $fileName = time() . "_" . basename($_FILES['profile_pic']['name']);
        $targetFile = $targetDir . $fileName;
        move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFile);
        $profile_pic = $targetFile;
    }

    $stmt = $conn->prepare("INSERT INTO users (username, email, phone, password, profile_pic) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $email, $phone, $password, $profile_pic);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        header("Location: feed.php");
        exit();
    } else {
        $error = "Registration failed! " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Anas Insta</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
        }

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

        body {
            background-color: var(--darker);
            color: var(--light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 80%, rgba(139, 92, 246, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(245, 158, 11, 0.1) 0%, transparent 50%);
            z-index: -1;
        }

        .container {
            display: flex;
            width: 100%;
            max-width: 1100px;
            min-height: 700px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, var(--dark), var(--darker));
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
            z-index: 0;
        }

        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
            font-size: 28px;
            font-weight: 700;
            z-index: 1;
            position: relative;
        }

        .logo i {
            margin-right: 12px;
            color: var(--primary);
            font-size: 32px;
        }

        .logo span {
            color: var(--primary);
        }

        .panel-title {
            font-size: 2.8rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 20px;
            background: linear-gradient(90deg, var(--light), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            z-index: 1;
            position: relative;
        }

        .panel-subtitle {
            font-size: 1.1rem;
            color: var(--gray);
            margin-bottom: 40px;
            line-height: 1.6;
            z-index: 1;
            position: relative;
        }

        .features {
            list-style: none;
            margin-top: 30px;
            z-index: 1;
            position: relative;
        }

        .features li {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            color: var(--gray);
        }

        .features i {
            color: var(--primary);
            margin-right: 15px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .right-panel {
            flex: 1;
            background-color: var(--card-bg);
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-left: 1px solid var(--card-border);
        }

        .form-container {
            max-width: 400px;
            width: 100%;
            margin: 0 auto;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-align: center;
        }

        .form-subtitle {
            color: var(--gray);
            text-align: center;
            margin-bottom: 40px;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 500;
        }

        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--light);
        }

        .input-with-icon {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 18px;
        }

        .form-control {
            width: 100%;
            padding: 15px 15px 15px 50px;
            background-color: rgba(15, 15, 19, 0.6);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            color: var(--light);
            font-size: 16px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
            background-color: rgba(15, 15, 19, 0.6);
            border: 1px dashed var(--card-border);
            border-radius: 10px;
            color: var(--gray);
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .file-upload-label:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .file-upload-label i {
            margin-right: 10px;
            font-size: 20px;
        }

        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            color: var(--light);
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn i {
            margin-right: 10px;
        }

        .login-link {
            text-align: center;
            margin-top: 30px;
            color: var(--gray);
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .login-link a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }

        /* Animation for form elements */
        .form-group {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s ease forwards;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }
        .form-group:nth-child(5) { animation-delay: 0.5s; }
        .form-group:nth-child(6) { animation-delay: 0.6s; }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 900px) {
            .container {
                flex-direction: column;
                max-width: 500px;
            }
            
            .left-panel {
                padding: 40px 30px;
            }
            
            .right-panel {
                padding: 40px 30px;
                border-left: none;
                border-top: 1px solid var(--card-border);
            }
            
            .panel-title {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .container {
                border-radius: 15px;
            }
            
            .left-panel, .right-panel {
                padding: 30px 20px;
            }
            
            .panel-title {
                font-size: 1.8rem;
            }
            
            .form-title {
                font-size: 1.6rem;
            }
        }

        /* File upload preview */
        .file-preview {
            margin-top: 10px;
            text-align: center;
            display: none;
        }

        .file-preview img {
            max-width: 100px;
            max-height: 100px;
            border-radius: 10px;
            border: 1px solid var(--card-border);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="logo">
                <i class="fas fa-camera-retro"></i>
                Anas <span>Insta</span>
            </div>
            <h1 class="panel-title">Join Our Creative Community</h1>
            <p class="panel-subtitle">Sign up to share your moments, connect with friends, and discover amazing content from around the world.</p>
            
            <ul class="features">
                <li><i class="fas fa-check-circle"></i> Share photos and videos with advanced filters</li>
                <li><i class="fas fa-check-circle"></i> Connect with friends and build your network</li>
                <li><i class="fas fa-check-circle"></i> Discover trending content and creators</li>
                <li><i class="fas fa-check-circle"></i> Access exclusive features and tools</li>
            </ul>
        </div>
        
        <div class="right-panel">
            <div class="form-container">
                <h2 class="form-title">Create Account</h2>
                <p class="form-subtitle">Join thousands of creators today</p>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" id="registerForm">
                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <div class="input-with-icon">
                            <i class="input-icon fas fa-user"></i>
                            <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-with-icon">
                            <i class="input-icon fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <div class="input-with-icon">
                            <i class="input-icon fas fa-phone"></i>
                            <input type="text" id="phone" name="phone" class="form-control" placeholder="Enter your phone number">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-with-icon">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Create a password" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Profile Picture</label>
                        <div class="file-upload">
                            <label class="file-upload-label" for="profile_pic">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Choose profile picture</span>
                            </label>
                            <input type="file" id="profile_pic" name="profile_pic" class="file-upload-input" accept="image/*">
                        </div>
                        <div class="file-preview" id="filePreview"></div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                    </div>
                </form>
                
                <div class="login-link">
                    Already have an account? <a href="login.php">Sign In</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // File upload preview
        document.getElementById('profile_pic').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('filePreview');
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                return false;
            }
            
            // If everything is valid, the form will submit
        });
    </script>
</body>
</html>