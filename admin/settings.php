<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_settings'])) {
        $whatsapp_number = sanitize($_POST['whatsapp_number']);
        $upi_id = sanitize($_POST['upi_id']);
        $site_title = sanitize($_POST['site_title']);
        $welcome_message = sanitize($_POST['welcome_message']);
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->execute([$whatsapp_number, 'whatsapp_number']);
            $stmt->execute([$upi_id, 'upi_id']);
            $stmt->execute([$site_title, 'site_title']);
            $stmt->execute([$welcome_message, 'welcome_message']);
            
            $pdo->commit();
            $success = 'Settings updated successfully!';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Failed to update settings.';
        }
    }
    
    if (isset($_POST['change_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password && $confirm_password) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE id = 1");
                    if ($stmt->execute([$new_password])) {
                        $success = 'Password changed successfully!';
                    } else {
                        $error = 'Failed to change password.';
                    }
                } else {
                    $error = 'Password must be at least 6 characters.';
                }
            } else {
                $error = 'Passwords do not match.';
            }
        } else {
            $error = 'Please fill all password fields.';
        }
    }
    
    if (isset($_POST['change_username'])) {
        $new_username = sanitize($_POST['new_username']);
        
        if ($new_username) {
            $stmt = $pdo->prepare("UPDATE admin SET username = ? WHERE id = 1");
            if ($stmt->execute([$new_username])) {
                $success = 'Username changed successfully!';
            } else {
                $error = 'Failed to change username.';
            }
        } else {
            $error = 'Please enter a username.';
        }
    }
}

// Get current settings
$stmt = $pdo->query("SELECT * FROM settings");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get current admin info
$admin = $pdo->query("SELECT * FROM admin WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - GT Online Class</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-nav {
            background: var(--gray-900);
            color: white;
            padding: var(--spacing-4);
        }
        .admin-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-brand {
            display: flex;
            align-items: center;
            gap: var(--spacing-3);
            font-size: var(--font-size-xl);
            font-weight: 600;
        }
        .admin-menu {
            display: flex;
            gap: var(--spacing-6);
        }
        .admin-menu a {
            color: white;
            text-decoration: none;
            padding: var(--spacing-2) var(--spacing-4);
            border-radius: var(--spacing-2);
            transition: var(--transition-fast);
        }
        .admin-menu a:hover, .admin-menu a.active {
            background: rgba(255,255,255,0.1);
        }
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
            padding: var(--spacing-8) var(--spacing-4);
        }
        .settings-sections {
            display: grid;
            gap: var(--spacing-8);
        }
        .settings-section {
            background: white;
            padding: var(--spacing-8);
            border-radius: var(--spacing-4);
            box-shadow: var(--shadow);
        }
        .section-title {
            font-size: var(--font-size-xl);
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: var(--spacing-6);
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }
        .section-title i {
            color: var(--primary-color);
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-4);
        }
        .success-message {
            background: #f0fdf4;
            color: #166534;
            padding: var(--spacing-3);
            border-radius: var(--spacing-2);
            margin-bottom: var(--spacing-4);
        }
        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: var(--spacing-3);
            border-radius: var(--spacing-2);
            margin-bottom: var(--spacing-4);
        }
        .current-info {
            background: var(--gray-50);
            padding: var(--spacing-4);
            border-radius: var(--spacing-2);
            margin-bottom: var(--spacing-4);
        }
        .current-info h4 {
            margin-bottom: var(--spacing-2);
            color: var(--gray-700);
        }
        @media (max-width: 768px) {
            .admin-menu { display: none; }
            .form-row { grid-template-columns: 1fr; }
            .settings-container { padding: var(--spacing-4); }
        }
    </style>
</head>
<body>
    <nav class="admin-nav">
        <div class="container">
            <div class="admin-brand">
                <i class="fas fa-user-shield"></i>
                <span>GT Admin Panel</span>
            </div>
            <div class="admin-menu">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="students.php"><i class="fas fa-users"></i> Students</a>
                <a href="videos.php"><i class="fas fa-video"></i> Videos</a>
                <a href="testimonials.php"><i class="fas fa-comments"></i> Reviews</a>
                <a href="users.php"><i class="fas fa-user-graduate"></i> Users</a>
                <a href="settings.php" class="active"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="settings-container">
        <h1 style="margin-bottom: var(--spacing-8); color: var(--gray-800);">
            <i class="fas fa-cog"></i> System Settings
        </h1>

        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="settings-sections">
            <!-- General Settings -->
            <div class="settings-section">
                <h2 class="section-title">
                    <i class="fas fa-globe"></i>
                    General Settings
                </h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="site_title">Site Title</label>
                        <input type="text" id="site_title" name="site_title" required 
                               value="<?php echo htmlspecialchars($settings['site_title'] ?? 'GT Online Class'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="welcome_message">Welcome Message</label>
                        <input type="text" id="welcome_message" name="welcome_message" required 
                               value="<?php echo htmlspecialchars($settings['welcome_message'] ?? 'Welcome to the Learning World'); ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="whatsapp_number">WhatsApp Help Number</label>
                            <input type="text" id="whatsapp_number" name="whatsapp_number" required 
                                   value="<?php echo htmlspecialchars($settings['whatsapp_number'] ?? '+919876543210'); ?>"
                                   placeholder="+919876543210">
                        </div>
                        
                        <div class="form-group">
                            <label for="upi_id">UPI ID for Payments</label>
                            <input type="text" id="upi_id" name="upi_id" required 
                                   value="<?php echo htmlspecialchars($settings['upi_id'] ?? 'admin@paytm'); ?>"
                                   placeholder="admin@paytm">
                        </div>
                    </div>
                    
                    <button type="submit" name="update_settings" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Settings
                    </button>
                </form>
            </div>

            <!-- Admin Account Settings -->
            <div class="settings-section">
                <h2 class="section-title">
                    <i class="fas fa-user-cog"></i>
                    Admin Account
                </h2>
                
                <div class="current-info">
                    <h4>Current Admin Details</h4>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($admin['username']); ?></p>
                    <p><strong>Last Updated:</strong> <?php echo date('M j, Y H:i', strtotime($admin['created_at'])); ?></p>
                </div>
                
                <!-- Change Username -->
                <form method="POST" style="margin-bottom: var(--spacing-6);">
                    <h4 style="margin-bottom: var(--spacing-4);">Change Username</h4>
                    <div class="form-group">
                        <label for="new_username">New Username</label>
                        <input type="text" id="new_username" name="new_username" required 
                               value="<?php echo htmlspecialchars($admin['username']); ?>">
                    </div>
                    <button type="submit" name="change_username" class="btn btn-secondary">
                        <i class="fas fa-user"></i> Update Username
                    </button>
                </form>
                
                <!-- Change Password -->
                <form method="POST">
                    <h4 style="margin-bottom: var(--spacing-4);">Change Password</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                        </div>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-lock"></i> Change Password
                    </button>
                </form>
            </div>

            <!-- System Information -->
            <div class="settings-section">
                <h2 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    System Information
                </h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-4);">
                    <div class="current-info">
                        <h4>Database</h4>
                        <p><strong>Host:</strong> <?php echo DB_HOST; ?></p>
                        <p><strong>Database:</strong> <?php echo DB_NAME; ?></p>
                    </div>
                    
                    <div class="current-info">
                        <h4>File Upload</h4>
                        <p><strong>Max Size:</strong> <?php echo (MAX_FILE_SIZE / 1024 / 1024); ?>MB</p>
                        <p><strong>Upload Path:</strong> <?php echo UPLOAD_PATH; ?></p>
                    </div>
                    
                    <div class="current-info">
                        <h4>System</h4>
                        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                        <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="settings-section">
                <h2 class="section-title">
                    <i class="fas fa-bolt"></i>
                    Quick Actions
                </h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-4);">
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                    <a href="../index.php" class="btn btn-secondary" target="_blank">
                        <i class="fas fa-external-link-alt"></i> View Website
                    </a>
                    <a href="users.php" class="btn btn-secondary">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                    <a href="videos.php" class="btn btn-secondary">
                        <i class="fas fa-video"></i> Manage Videos
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>