<?php
// Installer for Expde Shop Admin - Install Folder Version
session_start();

// Check if already installed
if (file_exists('../.env') && file_exists('../storage/installed')) {
    header('Location: ../public/admin');
    exit;
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';


// Handle form submissions
if ($_POST) {
    // Get step from POST if available, otherwise from GET
    $post_step = $_POST['step'] ?? $step;
    
    
    switch ($post_step) {
        case 1:
            // Check PHP extensions and permissions
            $extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo'];
            $missing = [];
            foreach ($extensions as $ext) {
                if (!extension_loaded($ext)) {
                    $missing[] = $ext;
                }
            }
            
            $writable_dirs = ['../storage', '../bootstrap/cache'];
            $not_writable = [];
            foreach ($writable_dirs as $dir) {
                if (!is_writable($dir)) {
                    $not_writable[] = $dir;
                }
            }
            
            
            if (empty($missing) && empty($not_writable)) {
                $_SESSION['requirements_ok'] = true;
                header('Location: ?step=2');
                exit;
            } else {
                $error = 'Please fix the following issues:<br>';
                if (!empty($missing)) {
                    $error .= 'Missing PHP extensions: ' . implode(', ', $missing) . '<br>';
                }
                if (!empty($not_writable)) {
                    $error .= 'Not writable directories: ' . implode(', ', $not_writable) . '<br>';
                }
            }
            break;
            
        case 2:
            // Database configuration
            $db_host = $_POST['db_host'] ?? 'localhost';
            $db_port = $_POST['db_port'] ?? '3306';
            $db_name = $_POST['db_name'] ?? '';
            $db_user = $_POST['db_user'] ?? '';
            $db_pass = $_POST['db_pass'] ?? '';
            
            if (empty($db_name) || empty($db_user)) {
                $error = 'Database name and username are required.';
            } else {
                // Test database connection
                try {
                    $pdo = new PDO("mysql:host=$db_host;port=$db_port", $db_user, $db_pass);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Create database if it doesn't exist
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
                    $pdo->exec("USE `$db_name`");
                    
                    $_SESSION['db_config'] = [
                        'host' => $db_host,
                        'port' => $db_port,
                        'name' => $db_name,
                        'user' => $db_user,
                        'pass' => $db_pass
                    ];
                    
                    header('Location: ?step=3');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Database connection failed: ' . $e->getMessage();
                }
            }
            break;
            
        case 3:
            // Admin account creation
            $admin_name = $_POST['admin_name'] ?? '';
            $admin_email = $_POST['admin_email'] ?? '';
            $admin_password = $_POST['admin_password'] ?? '';
            $admin_confirm = $_POST['admin_confirm'] ?? '';
            
            if (empty($admin_name) || empty($admin_email) || empty($admin_password)) {
                $error = 'All admin account fields are required.';
            } elseif ($admin_password !== $admin_confirm) {
                $error = 'Passwords do not match.';
            } elseif (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email address.';
            } else {
                $_SESSION['admin_config'] = [
                    'name' => $admin_name,
                    'email' => $admin_email,
                    'password' => $admin_password
                ];
                
                header('Location: ?step=4');
                exit;
            }
            break;
            
        case 4:
            // Install application
            try {
                if (!isset($_SESSION['db_config']) || !isset($_SESSION['admin_config'])) {
                    throw new Exception('Missing configuration data. Please go back and complete all steps.');
                }
                
                $db_config = $_SESSION['db_config'];
                $admin_config = $_SESSION['admin_config'];
                
                // Generate .env file
                $env_content = "APP_NAME=\"Expde Shop Admin\"
APP_ENV=production
APP_KEY=" . base64_encode(random_bytes(32)) . "
APP_DEBUG=false
APP_URL=" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['REQUEST_URI'])) . "

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST={$db_config['host']}
DB_PORT={$db_config['port']}
DB_DATABASE={$db_config['name']}
DB_USERNAME={$db_config['user']}
DB_PASSWORD={$db_config['pass']}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=\"noreply@expdeshop.com\"
MAIL_FROM_NAME=\"Expde Shop Admin\"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY=\"\${PUSHER_APP_KEY}\"
VITE_PUSHER_HOST=\"\${PUSHER_HOST}\"
VITE_PUSHER_PORT=\"\${PUSHER_PORT}\"
VITE_PUSHER_SCHEME=\"\${PUSHER_SCHEME}\"
VITE_PUSHER_APP_CLUSTER=\"\${PUSHER_APP_CLUSTER}\"";
                
                $env_file = '../.env';
                if (file_put_contents($env_file, $env_content) === false) {
                    throw new Exception("Failed to create .env file at: " . $env_file);
                }
                
                // Test database connection first
                try {
                    $pdo = new PDO("mysql:host={$db_config['host']};port={$db_config['port']};dbname={$db_config['name']}", $db_config['user'], $db_config['pass']);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    // Test the connection
                    $pdo->query("SELECT 1");
                } catch (PDOException $e) {
                    throw new Exception("Database connection failed: " . $e->getMessage());
                }
                
                // Include and run migrations
                $migration_file = '../database/migrations/install.php';
                if (!file_exists($migration_file)) {
                    throw new Exception("Migration file not found: " . $migration_file);
                }
                include_once $migration_file;
                runMigrations($pdo);
                
                // Create admin user (only if doesn't exist)
                $hashed_password = password_hash($admin_config['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT IGNORE INTO users (name, email, password, role, email_verified_at, created_at, updated_at) VALUES (?, ?, ?, 'super_admin', NOW(), NOW(), NOW())");
                $stmt->execute([$admin_config['name'], $admin_config['email'], $hashed_password]);
                
                // Mark as installed
                $installed_file = '../storage/installed';
                if (file_put_contents($installed_file, date('Y-m-d H:i:s')) === false) {
                    throw new Exception("Failed to create installed marker file at: " . $installed_file);
                }
                
                // Clear any previous errors
                unset($error);
                header('Location: ?step=5');
                exit;
            } catch (Exception $e) {
                $error = 'Installation failed: ' . $e->getMessage();
            }
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#10b981" />
    <title>Expde Shop Admin - Installation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #334155;
            min-height: 100vh;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.05"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.05"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.05"/><circle cx="10" cy="60" r="0.5" fill="white" opacity="0.05"/><circle cx="90" cy="40" r="0.5" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            pointer-events: none;
        }
        
        .installer-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            position: relative;
            z-index: 1;
        }
        
        .installer-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }
        
        .installer-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .installer-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .installer-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 0 0 0.5rem 0;
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .installer-header p {
            font-size: 1.25rem;
            opacity: 0.95;
            margin: 0;
            position: relative;
            z-index: 2;
            font-weight: 500;
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 2rem 0;
            position: relative;
            z-index: 1;
        }
        
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
        }
        
        .step.active {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: 3px solid #fbbf24;
            transform: scale(1.15);
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.4);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { box-shadow: 0 10px 30px rgba(245, 158, 11, 0.4); }
            50% { box-shadow: 0 15px 40px rgba(245, 158, 11, 0.6); }
        }
        
        .step.completed {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }
        
        .step.pending {
            background: rgba(255, 255, 255, 0.2);
            color: rgba(255, 255, 255, 0.7);
        }
        
        .step-line {
            width: 60px;
            height: 2px;
            background: rgba(255, 255, 255, 0.3);
            margin: 0 0.5rem;
        }
        
        .step-line.completed {
            background: #10b981;
        }
        
        .installer-body {
            padding: 3rem;
        }
        
        .step-content {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .step-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .step-description {
            color: #6b7280;
            margin-bottom: 2rem;
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-text {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            justify-content: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }
        
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
        
        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1rem;
        }
        
        .requirement-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .requirement-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.5rem;
        }
        
        .requirement-section h5 {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .requirement-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0;
            font-size: 0.875rem;
        }
        
        .requirement-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .success-icon {
            color: #10b981;
        }
        
        .error-icon {
            color: #ef4444;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        
        .alert-warning {
            background: #fffbeb;
            border: 1px solid #fed7aa;
            color: #92400e;
        }
        
        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
        
        .help-panel {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
        
        .help-panel h6 {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .help-panel h6:not(:first-child) {
            margin-top: 1.5rem;
        }
        
        .help-panel ol, .help-panel ul {
            margin-left: 1.5rem;
            color: #4b5563;
        }
        
        .help-panel li {
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }
        
        .code-block {
            background: #1f2937;
            color: #f9fafb;
            padding: 1rem;
            border-radius: 6px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 0.8rem;
            margin: 0.5rem 0;
            overflow-x: auto;
        }
        
        .permission-fix {
            background: #fffbeb;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .permission-fix h6 {
            color: #92400e;
            margin-bottom: 1rem;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .permission-fix .row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        
        .permission-fix h6:not(:first-child) {
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
        }
        
        .permission-fix ol, .permission-fix ul {
            margin-left: 1.5rem;
            color: #92400e;
        }
        
        .permission-fix li {
            margin-bottom: 0.25rem;
            font-size: 0.8rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .debug-info {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 0.75rem;
            margin-top: 1rem;
            font-size: 0.75rem;
            color: #64748b;
        }
        
        .fallback-link {
            margin-top: 1rem;
            text-align: center;
        }
        
        .fallback-link a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: #475569;
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.2s ease;
        }
        
        .fallback-link a:hover {
            background: #e2e8f0;
            border-color: #94a3b8;
        }
        
        @media (max-width: 768px) {
            .installer-wrapper {
                padding: 1rem 0.5rem;
            }
            
            .installer-header {
                padding: 2rem 1rem;
            }
            
            .installer-header h1 {
                font-size: 2rem;
            }
            
            .installer-body {
                padding: 2rem 1rem;
            }
            
            .requirement-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .permission-fix .row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 1rem;
            }
            
            .step {
                width: 45px;
                height: 45px;
                font-size: 0.875rem;
            }
            
            .step-line {
                width: 50px;
            }
        }
        
        @media (max-width: 480px) {
            .installer-header {
                padding: 1.5rem;
            }
            
            .installer-header h1 {
                font-size: 1.5rem;
            }
            
            .installer-body {
                padding: 1.5rem 1rem;
            }
            
            .step {
                width: 28px;
                height: 28px;
                font-size: 0.7rem;
            }
            
            .step-line {
                width: 30px;
            }
        }
        .requirement-icon {
            margin-right: 15px;
            font-size: 1.2em;
        }
        .success-icon {
            color: #28a745;
        }
        .error-icon {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="installer-wrapper">
        <div class="installer-container">
            <div class="installer-header">
                <h1><i class="fas fa-shopping-cart"></i> Expde Shop Admin</h1>
                <p>Easy Installation Wizard</p>
                
                <div class="step-indicator">
                    <div class="step <?= $step >= 1 ? ($step == 1 ? 'active' : 'completed') : 'pending' ?>">1</div>
                    <div class="step-line <?= $step > 1 ? 'completed' : '' ?>"></div>
                    <div class="step <?= $step >= 2 ? ($step == 2 ? 'active' : 'completed') : 'pending' ?>">2</div>
                    <div class="step-line <?= $step > 2 ? 'completed' : '' ?>"></div>
                    <div class="step <?= $step >= 3 ? ($step == 3 ? 'active' : 'completed') : 'pending' ?>">3</div>
                    <div class="step-line <?= $step > 3 ? 'completed' : '' ?>"></div>
                    <div class="step <?= $step >= 4 ? ($step == 4 ? 'active' : 'completed') : 'pending' ?>">4</div>
                    <div class="step-line <?= $step > 4 ? 'completed' : '' ?>"></div>
                    <div class="step <?= $step >= 5 ? 'active' : 'pending' ?>">5</div>
                </div>
            </div>
            
            <div class="installer-body">
                <div class="step-content">
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?= $success ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($step == 1): ?>
                            <div class="step-title">
                                <i class="fas fa-cog"></i>
                                System Requirements Check
                            </div>
                            <div class="step-description">
                                Let's verify your server meets all requirements for Expde Shop Admin.
                            </div>
                                
                                <div class="requirement-grid">
                                    <div class="requirement-section">
                                        <h5 class="mb-3"><i class="fas fa-puzzle-piece"></i> PHP Extensions</h5>
                                        <?php
                                        $extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo'];
                                        $all_extensions_ok = true;
                                        foreach ($extensions as $ext):
                                            $loaded = extension_loaded($ext);
                                            if (!$loaded) $all_extensions_ok = false;
                                        ?>
                                            <div class="requirement-item">
                                                <i class="fas fa-<?= $loaded ? 'check-circle success-icon' : 'times-circle error-icon' ?> requirement-icon"></i>
                                                <span><?= $ext ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                        
                                        <?php if ($all_extensions_ok): ?>
                                            <div class="alert alert-success mt-3">
                                                <i class="fas fa-check-circle"></i> All PHP extensions are available!
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-danger mt-3">
                                                <i class="fas fa-exclamation-triangle"></i> Some PHP extensions are missing. Contact your hosting provider.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="requirement-section">
                                        <h5 class="mb-3"><i class="fas fa-folder-open"></i> Directory Permissions</h5>
                                        <?php
                                        $writable_dirs = ['../storage', '../bootstrap/cache'];
                                        $all_writable = true;
                                        foreach ($writable_dirs as $dir):
                                            $writable = is_writable($dir);
                                            if (!$writable) $all_writable = false;
                                        ?>
                                            <div class="requirement-item">
                                                <i class="fas fa-<?= $writable ? 'check-circle success-icon' : 'times-circle error-icon' ?> requirement-icon"></i>
                                                <span><?= $dir ?> (<?= $writable ? 'Writable' : 'Not Writable' ?>)</span>
                                            </div>
                                        <?php endforeach; ?>
                                        
                                        <?php if ($all_writable): ?>
                                            <div class="alert alert-success mt-3">
                                                <i class="fas fa-check-circle"></i> All directories are writable!
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning mt-3">
                                                <i class="fas fa-exclamation-triangle"></i> Some directories need permission fixes.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if (!$all_writable): ?>
                                <div class="permission-fix">
                                    <h6><i class="fas fa-tools"></i> How to Fix Directory Permissions</h6>
                                    <p class="mb-3">You need to make these folders writable before continuing:</p>
                                    
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h6><i class="fas fa-mouse-pointer"></i> Method 1: File Manager</h6>
                                            <ol class="small">
                                                <li>Go to your hosting control panel</li>
                                                <li>Open <strong>File Manager</strong></li>
                                                <li>Navigate to your <code>v2</code> folder</li>
                                                <li>Right-click on <code>storage</code> folder</li>
                                                <li>Select <strong>"Permissions"</strong></li>
                                                <li>Set to <code>755</code> or <code>777</code></li>
                                                <li>Repeat for <code>bootstrap/cache</code></li>
                                            </ol>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <h6><i class="fas fa-terminal"></i> Method 2: SSH/Terminal</h6>
                                            <div class="code-block">
chmod 755 storage<br>
chmod 755 bootstrap/cache<br>
chmod -R 755 storage/*<br>
chmod -R 755 bootstrap/cache/*
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <h6><i class="fas fa-headset"></i> Method 3: Contact Support</h6>
                                            <p class="small">If you can't change permissions yourself, contact your hosting provider and ask them to make these directories writable:</p>
                                            <ul class="small">
                                                <li><code>storage</code> folder</li>
                                                <li><code>bootstrap/cache</code> folder</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                            <div class="form-actions">
                                <div></div>
                                <form method="POST" action="" id="step1Form" style="display: inline;">
                                    <input type="hidden" name="step" value="1">
                                    <button type="submit" class="btn btn-primary btn-lg" id="continueBtn" <?= !$all_writable ? 'disabled' : '' ?>>
                                        <i class="fas fa-arrow-right"></i> 
                                        <?= $all_writable ? 'Continue to Database Setup' : 'Fix Permissions First' ?>
                                    </button>
                                </form>
                            </div>
                            
                            <script>
                            document.getElementById('step1Form').addEventListener('submit', function(e) {
                                document.getElementById('continueBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                                document.getElementById('continueBtn').disabled = true;
                            });
                            </script>
                            
                            <?php if (!$all_writable): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle"></i> 
                                After fixing permissions, refresh this page to continue.
                            </div>
                            <?php endif; ?>
                            
                            </div>
                            
                        <?php elseif ($step == 2): ?>
                            <div class="step-title">
                                <i class="fas fa-database"></i>
                                Database Configuration
                            </div>
                            <div class="step-description">
                                Enter your database connection details to continue with the installation.
                            </div>
                            
                            <form method="POST">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="db_host" class="form-label">
                                            <i class="fas fa-server"></i> Database Host
                                        </label>
                                        <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                                        <div class="form-text">Usually 'localhost' for shared hosting</div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="db_port" class="form-label">
                                            <i class="fas fa-plug"></i> Database Port
                                        </label>
                                        <input type="text" class="form-control" id="db_port" name="db_port" value="3306" required>
                                        <div class="form-text">Default MySQL port is 3306</div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="db_name" class="form-label">
                                        <i class="fas fa-database"></i> Database Name
                                    </label>
                                    <input type="text" class="form-control" id="db_name" name="db_name" placeholder="expde_shop" required>
                                    <div class="form-text">The name of your MySQL database</div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="db_user" class="form-label">
                                            <i class="fas fa-user"></i> Database Username
                                        </label>
                                        <input type="text" class="form-control" id="db_user" name="db_user" required>
                                        <div class="form-text">Your MySQL username</div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="db_pass" class="form-label">
                                            <i class="fas fa-lock"></i> Database Password
                                        </label>
                                        <input type="password" class="form-control" id="db_pass" name="db_pass">
                                        <div class="form-text">Your MySQL password</div>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <a href="?step=1" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-arrow-right"></i> Continue to Admin Setup
                                    </button>
                                </div>
                            </form>
                            
                            <div class="help-panel">
                                <h6><i class="fas fa-info-circle"></i> Database Setup Guide</h6>
                                
                                <h6><i class="fas fa-cpanel"></i> Method 1: cPanel</h6>
                                <ol>
                                    <li>Go to your hosting control panel</li>
                                    <li>Find <strong>"MySQL Databases"</strong></li>
                                    <li>Create a new database</li>
                                    <li>Create a database user</li>
                                    <li>Add user to database with <strong>all privileges</strong></li>
                                </ol>
                                
                                <h6><i class="fas fa-table"></i> Method 2: phpMyAdmin</h6>
                                <ol>
                                    <li>Open <strong>phpMyAdmin</strong></li>
                                    <li>Click <strong>"New"</strong> to create database</li>
                                    <li>Enter database name</li>
                                    <li>Click <strong>"Create"</strong></li>
                                </ol>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-lightbulb"></i>
                                    <strong>Tip:</strong> Use a strong password for your database user and keep these credentials secure.
                                </div>
                            </div>
                            
                        <?php elseif ($step == 3): ?>
                            <div class="step-title">
                                <i class="fas fa-user-shield"></i>
                                Admin Account Setup
                            </div>
                            <div class="step-description">
                                Create your administrator account to access the admin panel.
                            </div>
                            
                            <form method="POST">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="admin_name" class="form-label">
                                            <i class="fas fa-user"></i> Full Name
                                        </label>
                                        <input type="text" class="form-control" id="admin_name" name="admin_name" required>
                                        <div class="form-text">Your display name in the admin panel</div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="admin_email" class="form-label">
                                            <i class="fas fa-envelope"></i> Email Address
                                        </label>
                                        <input type="email" class="form-control" id="admin_email" name="admin_email" required>
                                        <div class="form-text">This will be your login username</div>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="admin_password" class="form-label">
                                            <i class="fas fa-lock"></i> Password
                                        </label>
                                        <input type="password" class="form-control" id="admin_password" name="admin_password" required minlength="8">
                                        <div class="form-text">Minimum 8 characters, use a strong password</div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="admin_confirm" class="form-label">
                                            <i class="fas fa-lock"></i> Confirm Password
                                        </label>
                                        <input type="password" class="form-control" id="admin_confirm" name="admin_confirm" required>
                                        <div class="form-text">Re-enter your password to confirm</div>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <a href="?step=2" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-arrow-right"></i> Complete Installation
                                    </button>
                                </div>
                            </form>
                            
                            <div class="help-panel">
                                <h6><i class="fas fa-shield-alt"></i> Security Tips</h6>
                                
                                <h6><i class="fas fa-key"></i> Password Requirements:</h6>
                                <ul>
                                    <li>Minimum 8 characters</li>
                                    <li>Use uppercase and lowercase letters</li>
                                    <li>Include numbers and special characters</li>
                                    <li>Avoid common words or patterns</li>
                                </ul>
                                
                                <h6><i class="fas fa-crown"></i> Admin Account Features:</h6>
                                <ul>
                                    <li><strong>Super Admin:</strong> Full system access</li>
                                    <li>Manage all products and orders</li>
                                    <li>Access to all settings</li>
                                    <li>User management capabilities</li>
                                    <li>System configuration access</li>
                                </ul>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Note:</strong> You can create additional admin accounts later from the admin panel.
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Important:</strong> Keep your login credentials safe. This account has full access to your admin panel.
                                </div>
                            </div>
                            
                        <?php elseif ($step == 4): ?>
                            <h3><i class="fas fa-download"></i> Installation</h3>
                            <p>Ready to install Expde Shop Admin. This may take a few moments.</p>
                            
                            <?php if (isset($error) && !empty($error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Installation Error:</strong> <?= htmlspecialchars($error) ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($success)): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Success:</strong> <?= htmlspecialchars($success) ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-3" role="status" id="installSpinner" style="display: none;">
                                    <span class="visually-hidden">Installing...</span>
                                </div>
                                <p id="installText">Click "Install Now" to begin the installation process.</p>
                            </div>
                            
                            <form method="POST" id="installForm" action="">
                                <input type="hidden" name="step" value="4">
                                <div class="d-flex justify-content-between">
                                    <a href="?step=3" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="installBtn">
                                        <i class="fas fa-download"></i> Install Now
                                    </button>
                                </div>
                            </form>
                            
                            <script>
                            document.getElementById('installForm').addEventListener('submit', function(e) {
                                document.getElementById('installSpinner').style.display = 'block';
                                document.getElementById('installText').textContent = 'Installing database tables and creating admin account...';
                                document.getElementById('installBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Installing...';
                                document.getElementById('installBtn').disabled = true;
                            });
                            </script>
                            
                        <?php elseif ($step == 5): ?>
                            <div class="text-center">
                                <div class="mb-4">
                                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                                </div>
                                <h3 class="text-success">Installation Complete!</h3>
                                <p class="text-muted">Expde Shop Admin has been successfully installed on your server.</p>
                                
                                <div class="alert alert-info">
                                    <h5><i class="fas fa-info-circle"></i> Important Security Note</h5>
                                    <p class="mb-0">For security reasons, please delete the <code>install</code> folder from your server after installation.</p>
                                </div>
                                
                                <a href="/admin/login.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt"></i> Go to Admin Panel
                                </a>
                            </div>
                        <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
