<?php
session_start();
require_once 'config/db.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            // Check if user exists and is active
            $stmt = $pdo->prepare("SELECT u.*, 
                                   CONCAT(e.first_name, ' ', e.last_name) as full_name,
                                   e.department_id,
                                   d.name as department_name
                                   FROM users u
                                   LEFT JOIN employees e ON u.employee_id = e.employee_id
                                   LEFT JOIN departments d ON e.department_id = d.department_id
                                   WHERE u.username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'] ?? $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['employee_id'] = $user['employee_id'];
                $_SESSION['department_name'] = $user['department_name'] ?? '';
                
                // Update last login (if you have that column)
                // $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                // $stmt->execute([$user['id']]);
                
                // Redirect based on role
                switch($user['role']) {
                    case 'admin':
                    case 'finance':
                    case 'hr':
                        header('Location: pages/dashboard.php');
                        break;
                    case 'employee':
                    default:
                        header('Location: pages/dashboard.php');
                        break;
                }
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Management System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-green: #1e6b3e;
            --primary-green-dark: #14492a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #1e6b3e 0%, #14492a 50%, #0f3b1e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .login-header {
            background: white;
            padding: 2.5rem 2rem 1.5rem;
            text-align: center;
        }

        .login-header .icon-circle {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #1e6b3e 0%, #14492a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
        }

        .login-header .icon-circle i {
            font-size: 2rem;
            color: white;
        }

        .login-header h2 {
            font-weight: 700;
            color: #1e6b3e;
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }

        .login-header p {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .login-body {
            padding: 0 2rem 2.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }

        .input-group {
            margin-bottom: 1.25rem;
        }

        .input-group-text {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-right: none;
            border-radius: 10px 0 0 10px;
            padding: 0.7rem 1rem;
            color: #6b7280;
        }

        .form-control {
            border: 2px solid #e5e7eb;
            padding: 0.7rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            border-radius: 0 10px 10px 0;
            border-left: none;
        }

        .form-control:focus {
            border-color: #1e6b3e;
            box-shadow: none;
            outline: none;
        }

        .input-group:focus-within .input-group-text {
            border-color: #1e6b3e;
        }

        .input-group:focus-within .form-control {
            border-color: #1e6b3e;
        }

        .password-toggle {
            cursor: pointer;
            padding: 0.7rem 1rem;
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-left: none;
            border-radius: 0 10px 10px 0;
            color: #6b7280;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: #1e6b3e;
            background: #f0fdf4;
        }

        .btn-login {
            background: linear-gradient(135deg, #1e6b3e 0%, #14492a 100%);
            border: none;
            padding: 0.8rem;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            width: 100%;
            color: white;
            cursor: pointer;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 107, 62, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 10px;
            padding: 0.9rem 1rem;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            border: none;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .demo-section {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 10px;
            text-align: center;
        }

        .demo-section h6 {
            color: #374151;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .demo-section .badge {
            font-size: 0.7rem;
            padding: 0.35rem 0.7rem;
            margin: 0.15rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .demo-section .badge:hover {
            transform: scale(1.05);
        }

        .footer-text {
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.8rem;
            margin-top: 1.25rem;
        }

        @media (max-width: 480px) {
            .login-card {
                border-radius: 16px;
            }
            .login-header {
                padding: 2rem 1.5rem 1rem;
            }
            .login-body {
                padding: 0 1.5rem 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="icon-circle">
                    <i class="bi bi-bank2"></i>
                </div>
                <h2>Finance Management System</h2>
                <p>Sign in to your account</p>
            </div>
            
            <div class="login-body">
                <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-start" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2 mt-0.5"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" autocomplete="off">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person-fill"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   placeholder="Enter your username"
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   required 
                                   autofocus
                                   autocomplete="off">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password"
                                   required
                                   autocomplete="off">
                            <span class="password-toggle" onclick="togglePassword()" title="Show/Hide password">
                                <i class="bi bi-eye-slash" id="toggleIcon"></i>
                            </span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                </form>
                
                <div class="demo-section">
                    <h6><i class="bi bi-info-circle me-1"></i>Demo Credentials (click to fill)</h6>
                    <span class="badge bg-success" onclick="fillLogin('admin')">admin</span>
                    <span class="badge bg-primary" onclick="fillLogin('john.smith')">john.smith</span>
                    <span class="badge bg-info" onclick="fillLogin('sarah.johnson')">sarah.johnson</span>
                    <span class="badge bg-warning text-dark" onclick="fillLogin('finance_head')">finance_head</span>
                </div>
            </div>
        </div>
        
        <p class="footer-text">
            <i class="bi bi-shield-lock me-1"></i>
            &copy; <?php echo date('Y'); ?> Finance Management System
        </p>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            }
        }
        
        function fillLogin(username) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = 'password123';
            
            // Highlight fields briefly
            const fields = ['username', 'password'];
            fields.forEach(function(id) {
                const el = document.getElementById(id);
                el.style.borderColor = '#22c55e';
                el.style.backgroundColor = '#f0fdf4';
                setTimeout(function() {
                    el.style.borderColor = '';
                    el.style.backgroundColor = '';
                }, 800);
            });
        }
        
        // Clear password on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('password').value = '';
        });
    </script>
</body>
</html>