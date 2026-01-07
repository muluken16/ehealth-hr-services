<?php
session_start();
// Check if already logged in
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    $redirectUrl = 'index.html';
    if ($role === 'admin') $redirectUrl = 'admin_dashboard.php';
    else if (strpos($role, 'zone') !== false) $redirectUrl = $role . '/' . $role . '_dashboard.php';
    else if (strpos($role, 'wereda') !== false) $redirectUrl = $role . '/' . $role . '_dashboard.php';
    else if (strpos($role, 'kebele') !== false) $redirectUrl = $role . '/' . $role . '_dashboard.php';
    
    header("Location: $redirectUrl");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>HealthFirst | Professional Access</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2a6e8c;
            --primary-light: #4cb5ae;
            --secondary: #1a4a5f;
            --accent: #ff7e5f;
            --bg-glass: rgba(255, 255, 255, 0.9);
            --text-main: #2c3e50;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f4f8;
            overflow: hidden;
            transition: background 0.3s ease;
        }

        .login-container {
            display: flex;
            width: 100%;
            max-width: 950px;
            min-height: 650px; /* Increased height for content */
            background: white;
            border-radius: 32px;
            box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            position: relative;
            margin: 20px;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Left Side - Image Section */
        .image-section {
            flex: 1; /* More balanced ratio */
            position: relative;
            background: url('assets/images/login-bg.png');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 60px;
            color: white;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .image-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        }

        .brand-overlay {
            position: relative;
            z-index: 1;
        }

        .brand-overlay h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -1px;
        }

        .brand-overlay p {
            font-size: 1.3rem;
            opacity: 0.9;
            font-weight: 300;
            max-width: 400px;
            line-height: 1.4;
        }

        /* Right Side - Form Section */
        .form-section {
            flex: 1;
            padding: 40px 50px; /* Adjusted padding */
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: var(--bg-glass);
            backdrop-filter: blur(15px);
            transition: all 0.5s ease;
            overflow-y: auto; /* Allow scrolling if content is too tall */
        }

        .form-header {
            margin-bottom: 35px; /* Reduced margin */
        }

        .form-header h2 {
            font-size: 2rem; /* Slightly smaller */
            color: var(--secondary);
            margin-bottom: 10px;
            font-weight: 700;
        }

        .form-header p {
            color: #64748b;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            padding-left: 45px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(42, 110, 140, 0.1);
        }

        .form-group i {
            position: absolute;
            left: 18px;
            top: 38px;
            color: #94a3b8;
            transition: color 0.3s;
            font-size: 0.9rem;
        }

        .form-group input:focus + i {
            color: var(--primary);
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 0.85rem;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: #64748b;
        }

        .forgot-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(42, 110, 140, 0.3);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(42, 110, 140, 0.4);
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .message {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: none;
            animation: slideDown 0.3s ease;
        }

        .message.error {
            background: #fff1f2;
            color: #e11d48;
            border: 1px solid #fda4af;
        }

        .message.success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #86efac;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive Breakpoints */
        @media (max-width: 1024px) {
            .login-container {
                max-width: 900px;
                min-height: 600px;
                height: auto;
            }
            .brand-overlay h1 { font-size: 2.8rem; }
            .form-section { padding: 40px; }
        }

        @media (max-width: 900px) {
            body { 
                background: #f0f4f8; /* Changed from white to give contrast */
                overflow-y: auto;
                height: auto;
                padding: 40px 20px;
            }
            .login-container {
                flex-direction: column;
                height: auto;
                min-height: unset; /* Allow it to wrap content */
                width: 100%;
                max-width: 500px; /* Center it like a card on mobile */
                margin: 0 auto;
                border-radius: 24px; /* Keep the premium rounded look */
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            }
            .image-section {
                flex: none;
                height: 220px; /* Slightly taller for visual balance */
                padding: 30px;
                border-radius: 24px 24px 0 0;
            }
            .brand-overlay h1 { font-size: 2.2rem; }
            .brand-overlay p { font-size: 1rem; }
            .form-section { 
                padding: 40px 30px;
                background: white;
                backdrop-filter: none;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 20px 10px; /* Slightly tighter on very small screens */
            }
            .image-section {
                height: 180px;
                padding: 25px;
            }
            .brand-overlay h1 { font-size: 1.8rem; }
            .form-header h2 { font-size: 1.6rem; }
            .form-section { padding: 30px 20px; }
        }

        /* Demo Login Section */
        .demo-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .demo-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            margin-bottom: 12px;
            font-weight: 700;
            text-align: center;
        }

        .demo-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .demo-chip {
            padding: 8px 10px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.75rem;
            color: #475569;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }

        .demo-chip:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side -->
        <div class="image-section">
            <div class="brand-overlay">
                <h1>HealthFirst</h1>
                <p>Advanced Medical Management System</p>
            </div>
        </div>

        <!-- Right Side -->
        <div class="form-section">
            <div class="form-header">
                <h2>Welcome Back</h2>
                <p>Please enter your credentials to access the system</p>
            </div>

            <div id="loginMessage" class="message"></div>

            <form id="loginForm">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" placeholder="name@healthfirst.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="remember-forgot">
                    <label class="checkbox-container">
                        <input type="checkbox" id="remember" name="remember">
                        Keep me logged in
                    </label>
                    <a href="#" class="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" class="login-btn" id="submitBtn">
                    Sign In
                </button>
            </form>

            <div class="demo-section">
                <p class="demo-title">Quick Demo Access</p>
                <div class="demo-grid">
                    <div class="demo-chip" style="grid-column: span 2;" onclick="fillLogin('admin@gmail.com', '123456')">Administrator</div>
                    <div class="demo-chip" onclick="fillLogin('zone_ho@gmail.com', '123456')">Zone HO</div>
                    <div class="demo-chip" onclick="fillLogin('zone_hr@gmail.com', '123456')">Zone HR</div>
                    <div class="demo-chip" onclick="fillLogin('wereda_ho@gmail.com', '123456')">Wereda HO</div>
                    <div class="demo-chip" onclick="fillLogin('wereda_hr@gmail.com', '123456')">Wereda HR</div>
                    <div class="demo-chip" onclick="fillLogin('kebele_ho@gmail.com', '123456')">Kebele HO</div>
                    <div class="demo-chip" onclick="fillLogin('kebele_hr@gmail.com', '123456')">Kebele HR</div>
                </div>
            </div>

            <div style="margin-top: 25px; text-align: center;">
                <p style="font-size: 0.85rem; color: #94a3b8;">
                    Need help? <a href="mailto:support@healthfirst.com" style="color: var(--primary); font-weight: 600; text-decoration: none;">IT Support</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function fillLogin(email, pass) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = pass;
            // Optionally auto-submit
            // document.getElementById('loginForm').dispatchEvent(new Event('submit'));
        }
        const loginForm = document.getElementById('loginForm');
        const loginMessage = document.getElementById('loginMessage');
        const submitBtn = document.getElementById('submitBtn');

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Clear message
            loginMessage.style.display = 'none';
            loginMessage.className = 'message';
            
            // Loading state
            submitBtn.textContent = 'Verifying...';
            submitBtn.disabled = true;

            const formData = new FormData(loginForm);
            
            try {
                const response = await fetch('login.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loginMessage.textContent = 'Access Granted! Redirecting...';
                    loginMessage.classList.add('success');
                    loginMessage.style.display = 'block';
                    
                    // Redirect based on role
                    let redirectUrl = 'index.html';
                    const role = data.role;
                    
                    if (role === 'admin') {
                        redirectUrl = 'admin_dashboard.php';
                    } else {
                        // Map role names to folder names
                        const folderMap = {
                            'zone_health_officer': 'zone_ho',
                            'zone_hr': 'zone_hr',
                            'wereda_health_officer': 'wereda_ho',
                            'wereda_hr': 'wereda_hr',
                            'kebele_health_officer': 'kebele_ho',
                            'kebele_hr': 'kebele_hr'
                        };
                        
                        const folder = folderMap[role] || role;
                        // Map folder name to specific dashboard filename if it differs from folder_dashboard.php
                        redirectUrl = `${folder}/${folder}_dashboard.php`;
                    }

                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 1200);
                } else {
                    loginMessage.textContent = data.message || 'Invalid credentials';
                    loginMessage.classList.add('error');
                    loginMessage.style.display = 'block';
                    submitBtn.textContent = 'Sign In';
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Login error:', error);
                loginMessage.textContent = 'System offline or connection error.';
                loginMessage.classList.add('error');
                loginMessage.style.display = 'block';
                submitBtn.textContent = 'Sign In';
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
