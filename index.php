<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anas Insta - Professional Social Platform</title>
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
        }

        body {
            background-color: var(--darker);
            color: var(--light);
            overflow-x: hidden;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background-color: rgba(15, 15, 19, 0.9);
            backdrop-filter: blur(10px);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid var(--card-border);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 26px;
            font-weight: 700;
            color: var(--light);
            text-decoration: none;
        }

        .logo span {
            color: var(--primary);
        }

        .logo i {
            margin-right: 10px;
            color: var(--primary);
            font-size: 28px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--light);
            font-weight: 500;
            transition: all 0.3s;
            position: relative;
        }

        .nav-links a:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            transition: width 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .nav-links a:hover:after {
            width: 100%;
        }

        .auth-buttons {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-size: 15px;
        }

        .btn-login {
            background-color: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-login:hover {
            background-color: rgba(139, 92, 246, 0.1);
            transform: translateY(-2px);
        }

        .btn-register {
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            color: var(--light);
            border: none;
            box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4);
        }

        /* Hero Section */
        .hero {
            padding: 180px 0 100px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            flex: 1;
            max-width: 550px;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.8rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 25px;
            background: linear-gradient(90deg, var(--light), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0;
            animation: fadeInUp 1s ease 0.2s forwards;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: var(--gray);
            margin-bottom: 35px;
            line-height: 1.7;
            opacity: 0;
            animation: fadeInUp 1s ease 0.4s forwards;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            margin-bottom: 40px;
            opacity: 0;
            animation: fadeInUp 1s ease 0.6s forwards;
        }

        .hero-btn {
            padding: 15px 32px;
            font-size: 1.05rem;
        }

        .hero-stats {
            display: flex;
            gap: 30px;
            opacity: 0;
            animation: fadeInUp 1s ease 0.8s forwards;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary);
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            color: var(--gray);
        }

        .hero-visual {
            flex: 1;
            display: flex;
            justify-content: flex-end;
            position: relative;
            z-index: 1;
        }

        .visual-container {
            position: relative;
            width: 500px;
            height: 500px;
        }

        .floating-card {
            position: absolute;
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--card-border);
            overflow: hidden;
            transition: transform 0.5s ease, opacity 0.5s ease;
        }

        .card-1 {
            width: 280px;
            height: 350px;
            top: 0;
            right: 0;
            animation: float 6s ease-in-out infinite;
            z-index: 3;
        }

        .card-2 {
            width: 240px;
            height: 300px;
            bottom: 0;
            left: 0;
            animation: float 6s ease-in-out 1s infinite;
            z-index: 2;
        }

        .card-3 {
            width: 200px;
            height: 250px;
            top: 50%;
            left: 50px;
            transform: translateY(-50%);
            animation: float 6s ease-in-out 2s infinite;
            z-index: 1;
        }

        .card-header {
            padding: 15px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--card-border);
        }

        .card-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .card-username {
            font-weight: 600;
            font-size: 15px;
        }

        .card-image {
            height: 180px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .card-actions {
            display: flex;
            justify-content: space-between;
            padding: 12px 15px;
        }

        .card-actions i {
            font-size: 20px;
            margin-right: 15px;
            color: var(--light);
            transition: color 0.3s;
        }

        .card-actions i:hover {
            color: var(--primary);
        }

        /* Features Section */
        .features {
            padding: 120px 0;
            background-color: var(--dark);
            position: relative;
        }

        .section-title {
            text-align: center;
            font-size: 2.8rem;
            margin-bottom: 80px;
            font-weight: 700;
            position: relative;
        }

        .section-title:after {
            content: '';
            position: absolute;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 40px;
        }

        .feature-card {
            background-color: var(--card-bg);
            border-radius: 16px;
            padding: 40px 30px;
            border: 1px solid var(--card-border);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(30px);
        }

        .feature-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
        }

        .feature-card:hover:before {
            transform: scaleX(1);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            color: white;
            font-size: 28px;
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3);
        }

        .feature-title {
            font-size: 1.5rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .feature-description {
            color: var(--gray);
            line-height: 1.7;
        }

        /* CTA Section */
        .cta {
            padding: 100px 0;
            text-align: center;
            background: linear-gradient(135deg, var(--dark), var(--darker));
            position: relative;
        }

        .cta-title {
            font-size: 2.8rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .cta-subtitle {
            font-size: 1.2rem;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto 40px;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        /* Footer */
        footer {
            background-color: var(--darker);
            padding: 70px 0 30px;
            border-top: 1px solid var(--card-border);
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }

        .footer-column {
            flex: 1;
            min-width: 200px;
            margin-bottom: 40px;
        }

        .footer-title {
            font-size: 1.3rem;
            margin-bottom: 25px;
            font-weight: 600;
            position: relative;
            display: inline-block;
        }

        .footer-title:after {
            content: '';
            position: absolute;
            width: 30px;
            height: 3px;
            background: var(--primary);
            bottom: -8px;
            left: 0;
            border-radius: 2px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            text-decoration: none;
            color: var(--gray);
            transition: color 0.3s;
            display: inline-block;
        }

        .footer-links a:hover {
            color: var(--primary);
            transform: translateX(5px);
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .social-links a {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background-color: var(--card-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--light);
            transition: all 0.3s;
            border: 1px solid var(--card-border);
        }

        .social-links a:hover {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(139, 92, 246, 0.3);
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid var(--card-border);
            color: var(--gray);
            font-size: 0.95rem;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-15px);
            }
            100% {
                transform: translateY(0px);
            }
        }

        /* Responsive Design */
        @media (max-width: 1100px) {
            .hero {
                flex-direction: column;
                text-align: center;
                padding: 150px 0 80px;
            }

            .hero-content {
                max-width: 100%;
                margin-bottom: 60px;
            }

            .hero-title {
                font-size: 3.2rem;
            }

            .hero-buttons {
                justify-content: center;
            }

            .visual-container {
                width: 100%;
                max-width: 500px;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 20px;
            }

            .nav-links {
                gap: 20px;
                flex-wrap: wrap;
                justify-content: center;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .section-title {
                font-size: 2.2rem;
            }

            .hero-stats {
                flex-wrap: wrap;
                justify-content: center;
            }

            .cta-title {
                font-size: 2.2rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
        }

        @media (max-width: 576px) {
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .hero-btn {
                width: 220px;
            }

            .floating-card {
                position: relative;
                margin: 0 auto 30px;
            }

            .card-1, .card-2, .card-3 {
                position: relative;
                top: auto;
                left: auto;
                right: auto;
                bottom: auto;
                transform: none;
                margin: 0 auto 30px;
            }

            .visual-container {
                height: auto;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="#" class="logo">
                    <i class="fas fa-camera-retro"></i>
                    Anas <span>Insta</span>
                </a>
                <div class="nav-links">
                    <a href="#">Home</a>
                    <a href="#">Features</a>
                    <a href="#">Explore</a>
                    <a href="#">Community</a>
                    <a href="#">Pricing</a>
                </div>
                <div class="auth-buttons">
                    <a href="login.php" class="btn btn-login">Sign In</a>
                    <a href="register.php" class="btn btn-register">Join Now</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Connect. Share. Inspire.</h1>
                <p class="hero-subtitle">Anas Insta is the professional social platform where creators and communities come together to share moments, build audiences, and tell their stories.</p>
                <div class="hero-buttons">
                    <a href="register.php" class="btn btn-register hero-btn">Create Account</a>
                    <a href="login.php" class="btn btn-login hero-btn">Sign In</a>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number">10M+</span>
                        <span class="stat-label">Active Users</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">500M+</span>
                        <span class="stat-label">Posts Shared</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">150+</span>
                        <span class="stat-label">Countries</span>
                    </div>
                </div>
            </div>
            <div class="hero-visual">
                <div class="visual-container">
                    <div class="floating-card card-1">
                        <div class="card-header">
                            <div class="card-avatar">AI</div>
                            <div class="card-username">anasinsta</div>
                        </div>
                        <div class="card-image">
                            <i class="fas fa-mountain"></i>
                        </div>
                        <div class="card-actions">
                            <div>
                                <i class="far fa-heart"></i>
                                <i class="far fa-comment"></i>
                                <i class="far fa-paper-plane"></i>
                            </div>
                            <i class="far fa-bookmark"></i>
                        </div>
                    </div>
                    <div class="floating-card card-2">
                        <div class="card-header">
                            <div class="card-avatar">JD</div>
                            <div class="card-username">johndoe</div>
                        </div>
                        <div class="card-image" style="background: linear-gradient(135deg, var(--secondary), #EAB308);">
                            <i class="fas fa-city"></i>
                        </div>
                        <div class="card-actions">
                            <div>
                                <i class="far fa-heart"></i>
                                <i class="far fa-comment"></i>
                                <i class="far fa-paper-plane"></i>
                            </div>
                            <i class="far fa-bookmark"></i>
                        </div>
                    </div>
                    <div class="floating-card card-3">
                        <div class="card-header">
                            <div class="card-avatar">ES</div>
                            <div class="card-username">emmasmith</div>
                        </div>
                        <div class="card-image" style="background: linear-gradient(135deg, var(--accent), #34D399);">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <div class="card-actions">
                            <div>
                                <i class="far fa-heart"></i>
                                <i class="far fa-comment"></i>
                                <i class="far fa-paper-plane"></i>
                            </div>
                            <i class="far fa-bookmark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2 class="section-title">Why Choose Anas Insta?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-camera"></i>
                    </div>
                    <h3 class="feature-title">Premium Content Sharing</h3>
                    <p class="feature-description">Share high-quality photos and videos with advanced filters and editing tools designed for professional creators.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="feature-title">Engage With Community</h3>
                    <p class="feature-description">Build meaningful connections with followers through comments, direct messages, and community features.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">Advanced Analytics</h3>
                    <p class="feature-description">Gain insights into your audience with detailed analytics to optimize your content strategy and grow your presence.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2 class="cta-title">Ready to Join Our Community?</h2>
            <p class="cta-subtitle">Sign up today and start sharing your world with millions of creative people around the globe.</p>
            <div class="cta-buttons">
                <a href="register.php" class="btn btn-register hero-btn">Get Started Free</a>
                <a href="login.php" class="btn btn-login hero-btn">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3 class="footer-title">Anas Insta</h3>
                    <p>A premium social platform for creators and communities to connect and share inspiring content.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="footer-column">
                    <h3 class="footer-title">Product</h3>
                    <ul class="footer-links">
                        <li><a href="#">Features</a></li>
                        <li><a href="#">Pricing</a></li>
                        <li><a href="#">API</a></li>
                        <li><a href="#">Download</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3 class="footer-title">Company</h3>
                    <ul class="footer-links">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Press</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3 class="footer-title">Support</h3>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Community</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2023 Anas Insta. All rights reserved. | Designed with <i class="fas fa-heart" style="color: var(--primary);"></i> for creators</p>
            </div>
        </div>
    </footer>

    <script>
        // Animation on scroll for feature cards
        document.addEventListener('DOMContentLoaded', function() {
            const featureCards = document.querySelectorAll('.feature-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, index * 200);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            
            featureCards.forEach(card => {
                observer.observe(card);
            });

            // Floating cards interaction
            const floatingCards = document.querySelectorAll('.floating-card');
            
            floatingCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05)';
                    this.style.zIndex = '10';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                    this.style.zIndex = '';
                });
            });
        });
    </script>
</body>
</html>