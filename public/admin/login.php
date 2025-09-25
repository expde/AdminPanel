<?php
// Admin Login Page
session_start();

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /admin/index.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($email && $password) {
        try {
            // Load database configuration from .env
            $env_file = '../../.env';
            if (file_exists($env_file)) {
                $env_content = file_get_contents($env_file);
                $db_config = [];
                
                // Parse .env file
                $lines = explode("\n", $env_content);
                foreach ($lines as $line) {
                    if (strpos($line, '=') !== false) {
                        list($key, $value) = explode('=', $line, 2);
                        $db_config[trim($key)] = trim($value);
                    }
                }
                
                // Connect to database
                $pdo = new PDO(
                    "mysql:host={$db_config['DB_HOST']};port={$db_config['DB_PORT']};dbname={$db_config['DB_DATABASE']}", 
                    $db_config['DB_USERNAME'], 
                    $db_config['DB_PASSWORD']
                );
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Check user credentials
                $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? AND role IN ('super_admin', 'admin', 'staff')");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    // Login successful
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_name'] = $user['name'];
                    $_SESSION['admin_email'] = $user['email'];
                    $_SESSION['admin_role'] = $user['role'];
                    
                    // Handle remember me functionality
                    if (isset($_POST['remember']) && $_POST['remember'] == 'on') {
                        // Set cookie for 30 days
                        setcookie('admin_email', $email, time() + (30 * 24 * 60 * 60), '/');
                        setcookie('admin_remember', '1', time() + (30 * 24 * 60 * 60), '/');
                    } else {
                        // Clear remember me cookies
                        setcookie('admin_email', '', time() - 3600, '/');
                        setcookie('admin_remember', '', time() - 3600, '/');
                    }
                    
                    header('Location: /admin/index.php');
                    exit;
                } else {
                    $error = 'Invalid email or password.';
                }
            } else {
                $error = 'Configuration file not found. Please run the installer first.';
            }
        } catch (Exception $e) {
            $error = 'Login failed: ' . $e->getMessage();
        }
    } else {
        $error = 'Please enter both email and password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1f2937">
    <meta name="description" content="Expde Shop Admin Panel - Secure login for administrators">
    <meta name="keywords" content="admin, login, ecommerce, shop, management">
    <meta name="author" content="Expde Shop">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login | Expde Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 20%, rgba(31, 41, 55, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(31, 41, 55, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(31, 41, 55, 0.02) 0%, transparent 50%);
            pointer-events: none;
        }

        .login-wrapper {
            width: 100%;
            max-width: 400px;
            position: relative;
        }

        .login-container {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 
                0 10px 25px -3px rgba(0, 0, 0, 0.1),
                0 4px 6px -2px rgba(0, 0, 0, 0.05),
                0 0 0 1px rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(31, 41, 55, 0.1);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .login-header {
            background: #1f2937;
            padding: 32px 24px;
            text-align: center;
            border-bottom: 1px solid #374151;
        }

        .login-header i {
            font-size: 2rem;
            color: #ffffff;
            margin-bottom: 12px;
            display: block;
        }

        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 4px;
            letter-spacing: -0.025em;
        }

        .login-header p {
            font-size: 0.875rem;
            color: #d1d5db;
            font-weight: 400;
        }

        .login-form {
            padding: 32px 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 6px;
            font-size: 0.875rem;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 0.875rem;
            z-index: 2;
        }

        .form-control {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.875rem;
            background: #ffffff;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-control:focus {
            border-color: #1f2937;
            box-shadow: 0 0 0 3px rgba(31, 41, 55, 0.1);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 24px;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            accent-color: #1f2937;
        }

        .remember-me label {
            font-size: 0.875rem;
            color: #6b7280;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            background: #1f2937;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            background: #111827;
        }

        .btn-login:active {
            transform: translateY(1px);
        }

        .security-note {
            text-align: center;
            margin-top: 24px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .security-note i {
            color: #6b7280;
            margin-right: 6px;
            font-size: 0.75rem;
        }

        .security-note span {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 400;
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .error-message i {
            font-size: 0.875rem;
        }

        /* Mobile Optimizations */
        @media (max-width: 768px) {
            body {
                padding: 16px;
            }

            .login-wrapper {
                max-width: 100%;
            }

            .login-container {
                border-radius: 12px;
            }

            .login-header {
                padding: 24px 20px;
            }

            .login-header h1 {
                font-size: 1.375rem;
            }

            .login-form {
                padding: 24px 20px;
            }

            .form-control {
                padding: 14px 14px 14px 44px;
                font-size: 1rem;
            }

            .input-group i {
                left: 14px;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 12px;
            }

            .login-container {
                border-radius: 8px;
            }

            .login-header {
                padding: 20px 16px;
            }

            .login-header h1 {
                font-size: 1.25rem;
            }

            .login-form {
                padding: 20px 16px;
            }

            .form-control {
                padding: 16px 16px 16px 48px;
                font-size: 1rem;
            }

            .input-group i {
                left: 16px;
                font-size: 1rem;
            }

            .btn-login {
                padding: 16px;
                font-size: 1rem;
            }
        }

        /* Extra small devices */
        @media (max-width: 360px) {
            body {
                padding: 8px;
            }

            .login-header {
                padding: 16px 12px;
            }

            .login-form {
                padding: 16px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-shopping-cart"></i>
                <h1>Expde Shop</h1>
                <p>Admin Panel</p>
            </div>
            
            <div class="login-form">
                <?php if ($error): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="Enter your email" required
                                   value="<?= htmlspecialchars($_POST['email'] ?? $_COOKIE['admin_email'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Enter your password" required>
                        </div>
                    </div>
                    
                    <div class="remember-me">
                        <input type="checkbox" id="remember" name="remember" 
                               <?= isset($_COOKIE['admin_email']) ? 'checked' : '' ?>>
                        <label for="remember">Remember me for 30 days</label>
                    </div>
                    
                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In to Dashboard
                    </button>
                </form>
                
                <div class="security-note">
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure Admin Access</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>