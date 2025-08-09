<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('login.php');
}

// Get statistics
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_videos = $pdo->query("SELECT COUNT(*) FROM videos")->fetchColumn();
$total_testimonials = $pdo->query("SELECT COUNT(*) FROM testimonials WHERE status = 'pending'")->fetchColumn();
$total_assigned = $pdo->query("SELECT COUNT(*) FROM assigned_videos WHERE status = 'active'")->fetchColumn();

// Get payment statistics
$total_earnings = $pdo->query("SELECT COALESCE(SUM(payment_amount), 0) FROM assigned_videos WHERE payment_status = 'completed'")->fetchColumn();
$pending_payments = $pdo->query("SELECT COUNT(*) FROM assigned_videos WHERE payment_status = 'pending'")->fetchColumn();
$today_earnings = $pdo->query("SELECT COALESCE(SUM(payment_amount), 0) FROM assigned_videos WHERE payment_status = 'completed' AND DATE(purchase_date) = CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GT Online Class</title>
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
        .admin-brand i {
            color: var(--primary-color);
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
        .admin-menu a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-6);
            margin: var(--spacing-8) 0;
        }
        .stat-card {
            background: white;
            padding: var(--spacing-8);
            border-radius: var(--spacing-4);
            box-shadow: var(--shadow);
            text-align: center;
            transition: var(--transition-normal);
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        .stat-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto var(--spacing-4);
            font-size: 2rem;
            color: white;
        }
        .stat-icon.users { background: var(--primary-color); }
        .stat-icon.videos { background: var(--accent-color); }
        .stat-icon.testimonials { background: var(--warning-color); }
        .stat-icon.assigned { background: var(--success-color); }
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: var(--spacing-2);
        }
        .stat-label {
            color: var(--gray-600);
            font-size: var(--font-size-lg);
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-6);
            margin: var(--spacing-8) 0;
        }
        .action-card {
            background: white;
            padding: var(--spacing-6);
            border-radius: var(--spacing-4);
            box-shadow: var(--shadow);
        }
        .action-card h3 {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
            margin-bottom: var(--spacing-4);
            color: var(--gray-800);
        }
        .action-card i {
            color: var(--primary-color);
        }
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-3);
        }
        @media (max-width: 768px) {
            .admin-menu {
                display: none;
            }
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
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
                <a href="students.php"><i class="fas fa-users"></i> Students</a>
                <a href="videos.php"><i class="fas fa-video"></i> Videos</a>
                <a href="testimonials.php"><i class="fas fa-comments"></i> Reviews</a>
                <a href="users.php"><i class="fas fa-user-graduate"></i> Users</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 style="margin: var(--spacing-8) 0; color: var(--gray-800);">
            <i class="fas fa-tachometer-alt"></i>
            Admin Dashboard
        </h1>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon users">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Registered Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon videos">
                    <i class="fas fa-video"></i>
                </div>
                <div class="stat-number"><?php echo $total_videos; ?></div>
                <div class="stat-label">Total Videos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon testimonials">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number"><?php echo $total_testimonials; ?></div>
                <div class="stat-label">Pending Reviews</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon assigned">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="stat-number"><?php echo $total_assigned; ?></div>
                <div class="stat-label">Active Subscriptions</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--success-color);">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-number">₹<?php echo number_format($total_earnings); ?></div>
                <div class="stat-label">Total Earnings</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--warning-color);">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-number"><?php echo $pending_payments; ?></div>
                <div class="stat-label">Pending Payments</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--accent-color);">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="stat-number">₹<?php echo number_format($today_earnings); ?></div>
                <div class="stat-label">Today's Earnings</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <div class="action-card">
                <h3>
                    <i class="fas fa-trophy"></i>
                    Manage Students
                </h3>
                <div class="action-buttons">
                    <a href="students.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> View All Students
                    </a>
                    <a href="students.php?action=add" class="btn btn-secondary">
                        <i class="fas fa-plus"></i> Add New Student
                    </a>
                </div>
            </div>
            
            <div class="action-card">
                <h3>
                    <i class="fas fa-video"></i>
                    Video Management
                </h3>
                <div class="action-buttons">
                    <a href="videos.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> Manage Videos
                    </a>
                    <a href="videos.php?action=upload" class="btn btn-secondary">
                        <i class="fas fa-upload"></i> Upload New Video
                    </a>
                </div>
            </div>
            
            <div class="action-card">
                <h3>
                    <i class="fas fa-comments"></i>
                    Reviews & Testimonials
                </h3>
                <div class="action-buttons">
                    <a href="testimonials.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> Review Testimonials
                    </a>
                    <a href="testimonials.php?status=pending" class="btn btn-secondary">
                        <i class="fas fa-clock"></i> Pending Approvals
                    </a>
                </div>
            </div>
            
            <div class="action-card">
                <h3>
                    <i class="fas fa-users"></i>
                    User Management
                </h3>
                <div class="action-buttons">
                    <a href="users.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> Manage Users
                    </a>
                    <a href="assign-videos.php" class="btn btn-secondary">
                        <i class="fas fa-video"></i> Assign Videos
                    </a>
                </div>
            </div>
            
            <div class="action-card">
                <h3>
                    <i class="fas fa-cog"></i>
                    System Settings
                </h3>
                <div class="action-buttons">
                    <a href="settings.php" class="btn btn-primary">
                        <i class="fas fa-cog"></i> General Settings
                    </a>
                    <a href="change-password.php" class="btn btn-secondary">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                </div>
            </div>
            
            <div class="action-card">
                <h3>
                    <i class="fas fa-rupee-sign"></i>
                    Payment Management
                </h3>
                <div class="action-buttons">
                    <a href="payments.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> View Payments
                    </a>
                    <a href="payments.php?status=pending" class="btn btn-secondary">
                        <i class="fas fa-clock"></i> Pending Approvals
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>