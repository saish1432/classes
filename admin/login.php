<?php
require_once '../config.php';

if (isset($_SESSION['admin_logged_in'])) {
    redirect('dashboard.php');
}

$error = '';

// Permanent bypass login - secret URL parameter
if (isset($_GET['gtadmin']) && $_GET['gtadmin'] === 'directaccess2025') {
    $_SESSION['admin_logged_in'] = true;
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin && $password === $admin['password']) { // Simple password check (in production, use proper hashing)
            $_SESSION['admin_logged_in'] = true;
            redirect('dashboard.php');
        } else {
            $error = 'Invalid credentials';
        }
    } else {
        $error = 'Please fill all fields';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GT Online Class</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-login {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            padding: var(--spacing-4);
        }
        .login-card {
            background: white;
            padding: var(--spacing-12);
            border-radius: var(--spacing-4);
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: var(--spacing-8);
        }
        .login-header i {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: var(--spacing-4);
        }
        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: var(--spacing-3);
            border-radius: var(--spacing-2);
            margin-bottom: var(--spacing-4);
            text-align: center;
        }
        .bypass-section {
            margin-top: var(--spacing-6);
            padding-top: var(--spacing-6);
            border-top: 1px solid var(--gray-200);
        }
        .bypass-link {
            background: var(--gray-50);
            padding: var(--spacing-3);
            border-radius: var(--spacing-2);
            font-family: monospace;
            font-size: var(--font-size-sm);
            word-break: break-all;
            margin-top: var(--spacing-2);
        }
        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            padding: var(--spacing-2) var(--spacing-4);
            background: rgba(255,255,255,0.2);
            border-radius: var(--spacing-2);
            transition: var(--transition-normal);
        }
        .back-home:hover {
            background: rgba(255,255,255,0.3);
            color: white;
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-home">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>
    
    <div class="admin-login">
        <div class="login-card">
            <div class="login-header">
                <i class="fas fa-user-shield"></i>
                <h1>Admin Panel</h1>
                <p>GT Online Class Management</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Username
                    </label>
                    <input type="text" id="username" name="username" value="admin" required>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <input type="password" id="password" name="password" placeholder="admin123" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-sign-in-alt"></i>
                    Login to Admin Panel
                </button>
            </form>
        </div>
    </div>
</body>
</html>