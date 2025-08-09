<?php
require_once '../config.php';

if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validate required fields
    if (!$name || !$email || !$mobile || !$password) {
        $error = 'All fields are required';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Email already registered';
        } else {
            // Register user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, mobile, password) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$name, $email, $mobile, $hashedPassword])) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration - GT Online Class</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-primary);
            padding: var(--spacing-4);
        }
        .register-form {
            background: white;
            padding: var(--spacing-12);
            border-radius: var(--spacing-4);
            box-shadow: var(--shadow-xl);
            width: 100%;
            max-width: 450px;
        }
        .register-header {
            text-align: center;
            margin-bottom: var(--spacing-8);
        }
        .register-header i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: var(--spacing-4);
        }
        .register-header h1 {
            color: var(--gray-800);
            margin-bottom: var(--spacing-2);
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
        .register-links {
            text-align: center;
            margin-top: var(--spacing-6);
        }
        .register-links a {
            color: var(--primary-color);
            text-decoration: none;
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
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-4);
        }
        @media (max-width: 480px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <a href="../index.php" class="back-home">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>
    
    <div class="register-container">
        <div class="register-form">
            <div class="register-header">
                <i class="fas fa-user-plus"></i>
                <h1>Student Registration</h1>
                <p>Join GT Online Class</p>
                <small style="color: var(--warning-color); font-weight: 600;">
                    <i class="fas fa-info-circle"></i>
                    Registration available only for paid users
                </small>
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
            
            <form method="POST">
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user"></i>
                        Full Name
                    </label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Email
                        </label>
                        <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="mobile">
                            <i class="fas fa-phone"></i>
                            Mobile
                        </label>
                        <input type="tel" id="mobile" name="mobile" required value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <input type="password" id="password" name="password" required minlength="6">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i>
                            Confirm
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-user-plus"></i>
                    Register
                </button>
            </form>
            
            <div class="register-links">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
</body>
</html>