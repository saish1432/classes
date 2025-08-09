<?php
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = sanitize($_POST['name'] ?? '');
        $mobile = sanitize($_POST['mobile'] ?? '');
        
        if ($name && $mobile) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, mobile = ? WHERE id = ?");
            if ($stmt->execute([$name, $mobile, $user_id])) {
                $_SESSION['user_name'] = $name;
                $success = 'Profile updated successfully!';
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = 'Failed to update profile.';
            }
        } else {
            $error = 'Please fill all required fields.';
        }
    }
    
    // Change password
    if (isset($_POST['change_password'])) {
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($newPassword && $confirmPassword) {
            if ($newPassword === $confirmPassword) {
                if (strlen($newPassword) >= 6) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    if ($stmt->execute([$hashedPassword, $user_id])) {
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - GT Online Class</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            padding: var(--spacing-8);
        }
        .profile-header {
            text-align: center;
            margin-bottom: var(--spacing-8);
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: 600;
            margin: 0 auto var(--spacing-4);
        }
        .profile-sections {
            display: grid;
            gap: var(--spacing-8);
        }
        .profile-section {
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
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-4);
        }
        .back-nav {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            margin-bottom: var(--spacing-6);
            text-decoration: none;
            color: var(--gray-600);
            transition: var(--transition-fast);
        }
        .back-nav:hover {
            color: var(--primary-color);
        }
        .success-message {
            background: #f0fdf4;
            color: #166534;
            padding: var(--spacing-3);
            border-radius: var(--spacing-2);
            margin-bottom: var(--spacing-4);
            text-align: center;
        }
        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: var(--spacing-3);
            border-radius: var(--spacing-2);
            margin-bottom: var(--spacing-4);
            text-align: center;
        }
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .profile-container {
                padding: var(--spacing-4);
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <a href="dashboard.php" class="back-nav">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>
        
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
            </div>
            <h1><?php echo $user['name']; ?></h1>
            <p style="color: var(--gray-600);">Member since <?php echo date('M Y', strtotime($user['registered_on'])); ?></p>
        </div>

        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="profile-sections">
            <!-- Profile Information -->
            <div class="profile-section">
                <h2 class="section-title">
                    <i class="fas fa-user"></i>
                    Profile Information
                </h2>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-user"></i>
                                Full Name
                            </label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i>
                                Email Address
                            </label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            <small style="color: var(--gray-500);">Email cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="mobile">
                                <i class="fas fa-phone"></i>
                                Mobile Number
                            </label>
                            <input type="tel" id="mobile" name="mobile" value="<?php echo htmlspecialchars($user['mobile']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <i class="fas fa-calendar"></i>
                                Registration Date
                            </label>
                            <input type="text" value="<?php echo date('M j, Y', strtotime($user['registered_on'])); ?>" disabled>
                        </div>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary" style="margin-top: var(--spacing-4);">
                        <i class="fas fa-save"></i>
                        Update Profile
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="profile-section">
                <h2 class="section-title">
                    <i class="fas fa-lock"></i>
                    Change Password
                </h2>
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="new_password">
                                <i class="fas fa-key"></i>
                                New Password
                            </label>
                            <input type="password" id="new_password" name="new_password" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">
                                <i class="fas fa-key"></i>
                                Confirm New Password
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                        </div>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-primary" style="margin-top: var(--spacing-4);">
                        <i class="fas fa-lock"></i>
                        Change Password
                    </button>
                </form>
            </div>

            <!-- Account Actions -->
            <div class="profile-section">
                <h2 class="section-title">
                    <i class="fas fa-cog"></i>
                    Account Actions
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-4);">
                    <a href="dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-tachometer-alt"></i>
                        Go to Dashboard
                    </a>
                    <a href="../index.php#lectures" class="btn btn-secondary">
                        <i class="fas fa-shopping-cart"></i>
                        Buy More Videos
                    </a>
                    <a href="https://wa.me/+919876543210" class="btn btn-secondary">
                        <i class="fab fa-whatsapp"></i>
                        Contact Support
                    </a>
                    <a href="logout.php" class="btn" style="background: var(--error-color); color: white;">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>