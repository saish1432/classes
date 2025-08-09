<?php
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get assigned videos
$stmt = $pdo->prepare("
    SELECT v.*, av.purchase_date, av.expiry_date, av.status as assignment_status
    FROM videos v 
    JOIN assigned_videos av ON v.id = av.video_id 
    WHERE av.user_id = ? AND av.status = 'active'
    ORDER BY av.purchase_date DESC
");
$stmt->execute([$user_id]);
$assigned_videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GT Online Class</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-nav {
            background: white;
            padding: var(--spacing-4);
            box-shadow: var(--shadow);
            margin-bottom: var(--spacing-8);
        }
        .dashboard-nav .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: var(--spacing-3);
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        .dashboard-content {
            padding: var(--spacing-8) 0;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: var(--spacing-8);
            margin-bottom: var(--spacing-8);
        }
        .profile-card {
            background: white;
            padding: var(--spacing-6);
            border-radius: var(--spacing-4);
            box-shadow: var(--shadow);
            height: fit-content;
        }
        .profile-card h3 {
            margin-bottom: var(--spacing-4);
            color: var(--gray-800);
        }
        .profile-info {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-3);
        }
        .profile-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-2);
        }
        .profile-item i {
            width: 20px;
            color: var(--primary-color);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: var(--spacing-4);
            margin-bottom: var(--spacing-8);
        }
        .stat-card {
            background: white;
            padding: var(--spacing-4);
            border-radius: var(--spacing-3);
            text-align: center;
            box-shadow: var(--shadow);
        }
        .stat-card i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: var(--spacing-2);
        }
        .stat-number {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            color: var(--gray-800);
        }
        .stat-label {
            font-size: var(--font-size-sm);
            color: var(--gray-600);
        }
        .video-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: var(--spacing-6);
        }
        .video-card {
            background: white;
            border-radius: var(--spacing-4);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition-normal);
        }
        .video-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        .video-player {
            position: relative;
            height: 180px;
            background: var(--gray-100);
        }
        .video-player video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .video-info {
            padding: var(--spacing-4);
        }
        .video-title {
            font-size: var(--font-size-lg);
            font-weight: 600;
            margin-bottom: var(--spacing-2);
            color: var(--gray-800);
        }
        .video-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: var(--font-size-sm);
            color: var(--gray-600);
        }
        .validity-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--spacing-1);
            padding: var(--spacing-1) var(--spacing-2);
            background: var(--success-color);
            color: white;
            border-radius: var(--spacing-1);
            font-size: var(--font-size-xs);
        }
        .expired-badge {
            background: var(--error-color);
        }
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            .user-info span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <nav class="dashboard-nav">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-graduation-cap"></i>
                <span>GT Online Class</span>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                </div>
                <span>Welcome, <?php echo $user['name']; ?>!</span>
                <div class="nav-links">
                    <a href="profile.php" class="btn btn-secondary" style="padding: var(--spacing-2) var(--spacing-4); font-size: var(--font-size-sm);">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="logout.php" class="btn btn-primary" style="padding: var(--spacing-2) var(--spacing-4); font-size: var(--font-size-sm);">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-content">
        <div class="container">
            <!-- Stats Section -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-video"></i>
                    <div class="stat-number"><?php echo count($assigned_videos); ?></div>
                    <div class="stat-label">Total Videos</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <div class="stat-number"><?php echo count(array_filter($assigned_videos, function($v) { return strtotime($v['expiry_date']) > time(); })); ?></div>
                    <div class="stat-label">Active</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-calendar"></i>
                    <div class="stat-number"><?php echo date('d', strtotime($user['registered_on'])); ?></div>
                    <div class="stat-label">Member Since</div>
                </div>
            </div>

            <div class="dashboard-grid">
                <!-- Profile Section -->
                <div class="profile-card">
                    <h3><i class="fas fa-user"></i> Profile Information</h3>
                    <div class="profile-info">
                        <div class="profile-item">
                            <i class="fas fa-user"></i>
                            <span><?php echo $user['name']; ?></span>
                        </div>
                        <div class="profile-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo $user['email']; ?></span>
                        </div>
                        <div class="profile-item">
                            <i class="fas fa-phone"></i>
                            <span><?php echo $user['mobile']; ?></span>
                        </div>
                        <div class="profile-item">
                            <i class="fas fa-calendar"></i>
                            <span>Joined <?php echo date('M Y', strtotime($user['registered_on'])); ?></span>
                        </div>
                    </div>
                    <a href="profile.php" class="btn btn-primary" style="width: 100%; margin-top: var(--spacing-4);">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>

                <!-- Quick Actions -->
                <div class="profile-card">
                    <h3><i class="fas fa-rocket"></i> Quick Actions</h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--spacing-3); margin-top: var(--spacing-4);">
                        <a href="../index.php#lectures" class="btn btn-secondary">
                            <i class="fas fa-shopping-cart"></i> Buy Videos
                        </a>
                        <a href="../index.php#testimonials" class="btn btn-secondary">
                            <i class="fas fa-star"></i> Rate Us
                        </a>
                        <a href="../index.php" class="btn btn-secondary">
                            <i class="fas fa-home"></i> Homepage
                        </a>
                        <a href="https://wa.me/+919876543210" class="btn btn-secondary">
                            <i class="fab fa-whatsapp"></i> Support
                        </a>
                    </div>
                </div>
            </div>

            <!-- Video Section -->
            <h2 class="section-title">
                <i class="fas fa-play-circle"></i>
                My Video Library
            </h2>

            <?php if (empty($assigned_videos)): ?>
                <div class="profile-card" style="text-align: center; padding: var(--spacing-12);">
                    <i class="fas fa-video" style="font-size: 4rem; color: var(--gray-300); margin-bottom: var(--spacing-4);"></i>
                    <h3 style="color: var(--gray-600); margin-bottom: var(--spacing-4);">No Videos Yet</h3>
                    <p style="color: var(--gray-500); margin-bottom: var(--spacing-6);">You haven't purchased any video lectures yet. Browse our collection and unlock premium content!</p>
                    <a href="../index.php#lectures" class="btn btn-primary">
                        <i class="fas fa-shopping-cart"></i> Browse Videos
                    </a>
                </div>
            <?php else: ?>
                <div class="video-grid">
                    <?php foreach ($assigned_videos as $video): ?>
                        <?php
                        $isExpired = strtotime($video['expiry_date']) < time();
                        $daysLeft = ceil((strtotime($video['expiry_date']) - time()) / (60 * 60 * 24));
                        ?>
                        <div class="video-card">
                            <div class="video-player">
                                <?php if (!$isExpired): ?>
                                    <video controls>
                                        <source src="../uploads/<?php echo $video['filename']; ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                <?php else: ?>
                                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: var(--gray-200); color: var(--gray-600);">
                                        <div style="text-align: center;">
                                            <i class="fas fa-lock" style="font-size: 2rem; margin-bottom: var(--spacing-2);"></i>
                                            <p>Expired</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="video-info">
                                <h3 class="video-title"><?php echo $video['title']; ?></h3>
                                <p style="color: var(--gray-600); margin-bottom: var(--spacing-3); font-size: var(--font-size-sm);">
                                    <?php echo substr($video['description'], 0, 100) . '...'; ?>
                                </p>
                                <div class="video-meta">
                                    <span>Purchased: <?php echo date('M j, Y', strtotime($video['purchase_date'])); ?></span>
                                    <span class="validity-badge <?php echo $isExpired ? 'expired-badge' : ''; ?>">
                                        <i class="fas fa-<?php echo $isExpired ? 'times' : 'check'; ?>"></i>
                                        <?php echo $isExpired ? 'Expired' : $daysLeft . ' days left'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>