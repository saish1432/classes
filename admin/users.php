<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_user'])) {
        $id = (int)$_POST['id'];
        $name = sanitize($_POST['name']);
        $email = sanitize($_POST['email']);
        $mobile = sanitize($_POST['mobile']);
        $status = sanitize($_POST['status']);
        
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, status = ? WHERE id = ?");
        if ($stmt->execute([$name, $email, $mobile, $status, $id])) {
            $success = 'User updated successfully!';
        } else {
            $error = 'Failed to update user.';
        }
    }
    
    if (isset($_POST['assign_video'])) {
        $user_id = (int)$_POST['user_id'];
        $video_id = (int)$_POST['video_id'];
        $days = (int)$_POST['days'];
        
        $expiry_date = date('Y-m-d H:i:s', strtotime("+$days days"));
        
        // Check if already assigned
        $stmt = $pdo->prepare("SELECT id FROM assigned_videos WHERE user_id = ? AND video_id = ? AND status = 'active'");
        $stmt->execute([$user_id, $video_id]);
        
        if ($stmt->fetch()) {
            $error = 'Video already assigned to this user.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO assigned_videos (user_id, video_id, expiry_date) VALUES (?, ?, ?)");
            if ($stmt->execute([$user_id, $video_id, $expiry_date])) {
                $success = 'Video assigned successfully!';
            } else {
                $error = 'Failed to assign video.';
            }
        }
    }
}

// Handle delete user
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = 'User deleted successfully!';
    } else {
        $error = 'Failed to delete user.';
    }
}

// Handle remove video assignment
if (isset($_GET['remove_video'])) {
    $id = (int)$_GET['remove_video'];
    $stmt = $pdo->prepare("DELETE FROM assigned_videos WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = 'Video access removed successfully!';
    } else {
        $error = 'Failed to remove video access.';
    }
}

// Get all users with their video assignments
$users = $pdo->query("
    SELECT u.*, 
           COUNT(av.id) as total_videos,
           COUNT(CASE WHEN av.status = 'active' AND av.expiry_date > NOW() THEN 1 END) as active_videos
    FROM users u 
    LEFT JOIN assigned_videos av ON u.id = av.user_id 
    GROUP BY u.id 
    ORDER BY u.registered_on DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Get all videos for assignment
$videos = $pdo->query("SELECT * FROM videos WHERE status = 'active' ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);

// Get user for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get user assignments
$user_assignments = [];
if (isset($_GET['view'])) {
    $user_id = (int)$_GET['view'];
    $stmt = $pdo->prepare("
        SELECT av.*, v.title, v.type, v.price, u.name as user_name
        FROM assigned_videos av 
        JOIN videos v ON av.video_id = v.id 
        JOIN users u ON av.user_id = u.id
        WHERE av.user_id = ? 
        ORDER BY av.purchase_date DESC
    ");
    $stmt->execute([$user_id]);
    $user_assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - GT Online Class</title>
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
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-8);
        }
        .users-table {
            background: white;
            border-radius: var(--spacing-4);
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: var(--spacing-4);
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }
        .table th {
            background: var(--gray-50);
            font-weight: 600;
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
        .status-badge {
            padding: var(--spacing-1) var(--spacing-2);
            border-radius: var(--spacing-1);
            font-size: var(--font-size-xs);
            font-weight: 600;
        }
        .status-active { background: var(--success-color); color: white; }
        .status-inactive { background: var(--error-color); color: white; }
        .video-count {
            display: flex;
            gap: var(--spacing-2);
        }
        .count-badge {
            padding: var(--spacing-1) var(--spacing-2);
            border-radius: var(--spacing-1);
            font-size: var(--font-size-xs);
            font-weight: 600;
            background: var(--gray-200);
            color: var(--gray-700);
        }
        .actions {
            display: flex;
            gap: var(--spacing-2);
        }
        .btn-sm {
            padding: var(--spacing-1) var(--spacing-3);
            font-size: var(--font-size-sm);
        }
        .form-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            overflow-y: auto;
        }
        .modal-content {
            background: white;
            padding: var(--spacing-8);
            border-radius: var(--spacing-4);
            width: 90%;
            max-width: 600px;
            margin: var(--spacing-4);
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
        .assignments-list {
            max-height: 400px;
            overflow-y: auto;
        }
        .assignment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--spacing-3);
            border-bottom: 1px solid var(--gray-200);
        }
        .assignment-info h5 {
            margin-bottom: var(--spacing-1);
        }
        .assignment-meta {
            font-size: var(--font-size-sm);
            color: var(--gray-600);
        }
        .expiry-badge {
            padding: var(--spacing-1) var(--spacing-2);
            border-radius: var(--spacing-1);
            font-size: var(--font-size-xs);
            font-weight: 600;
        }
        .expiry-active { background: var(--success-color); color: white; }
        .expiry-expired { background: var(--error-color); color: white; }
        @media (max-width: 768px) {
            .admin-menu { display: none; }
            .content-header { flex-direction: column; gap: var(--spacing-4); }
            .table { font-size: var(--font-size-sm); }
            .actions { flex-direction: column; }
            .form-row { grid-template-columns: 1fr; }
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
                <a href="users.php" class="active"><i class="fas fa-user-graduate"></i> Users</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding: var(--spacing-8) var(--spacing-4);">
        <div class="content-header">
            <h1><i class="fas fa-user-graduate"></i> User Management</h1>
            <button class="btn btn-primary" onclick="showAssignForm()">
                <i class="fas fa-video"></i> Assign Video
            </button>
        </div>

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

        <div class="users-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Contact</th>
                        <th>Videos</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: var(--spacing-3);">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($user['name']); ?></strong><br>
                                        <small style="color: var(--gray-600);">ID: <?php echo $user['id']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?><br>
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['mobile']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="video-count">
                                    <span class="count-badge">Total: <?php echo $user['total_videos']; ?></span>
                                    <span class="count-badge" style="background: var(--success-color); color: white;">Active: <?php echo $user['active_videos']; ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $user['status']; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($user['registered_on'])); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="?view=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <a href="?edit=<?php echo $user['id']; ?>" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="?delete=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm" 
                                       style="background: var(--error-color); color: white;"
                                       onclick="return confirm('Are you sure you want to delete this user?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit User Modal -->
    <?php if ($edit_user): ?>
    <div id="editModal" class="form-modal" style="display: flex;">
        <div class="modal-content">
            <h2>Edit User</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo htmlspecialchars($edit_user['name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo htmlspecialchars($edit_user['email']); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="mobile">Mobile</label>
                        <input type="tel" id="mobile" name="mobile" required 
                               value="<?php echo htmlspecialchars($edit_user['mobile']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="active" <?php echo ($edit_user['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($edit_user['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: flex; gap: var(--spacing-3); margin-top: var(--spacing-6);">
                    <button type="submit" name="update_user" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update User
                    </button>
                    <a href="users.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- View User Assignments Modal -->
    <?php if (!empty($user_assignments)): ?>
    <div id="viewModal" class="form-modal" style="display: flex;">
        <div class="modal-content">
            <h2>Video Assignments - <?php echo htmlspecialchars($user_assignments[0]['user_name']); ?></h2>
            <div class="assignments-list">
                <?php foreach ($user_assignments as $assignment): ?>
                    <?php
                    $isExpired = strtotime($assignment['expiry_date']) < time();
                    $daysLeft = ceil((strtotime($assignment['expiry_date']) - time()) / (60 * 60 * 24));
                    ?>
                    <div class="assignment-item">
                        <div class="assignment-info">
                            <h5><?php echo htmlspecialchars($assignment['title']); ?></h5>
                            <div class="assignment-meta">
                                <i class="fas fa-calendar"></i> Purchased: <?php echo date('M j, Y', strtotime($assignment['purchase_date'])); ?> |
                                <i class="fas fa-clock"></i> Expires: <?php echo date('M j, Y', strtotime($assignment['expiry_date'])); ?> |
                                <i class="fas fa-rupee-sign"></i> ₹<?php echo number_format($assignment['price']); ?>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: var(--spacing-2);">
                            <span class="expiry-badge expiry-<?php echo $isExpired ? 'expired' : 'active'; ?>">
                                <?php echo $isExpired ? 'Expired' : $daysLeft . ' days left'; ?>
                            </span>
                            <a href="?remove_video=<?php echo $assignment['id']; ?>" 
                               class="btn btn-sm" 
                               style="background: var(--error-color); color: white;"
                               onclick="return confirm('Remove video access?')">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="margin-top: var(--spacing-6);">
                <a href="users.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Users
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Assign Video Modal -->
    <div id="assignModal" class="form-modal" style="display: none;">
        <div class="modal-content">
            <h2>Assign Video to User</h2>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label for="user_id">Select User</label>
                        <select id="user_id" name="user_id" required>
                            <option value="">Choose User...</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="video_id">Select Video</label>
                        <select id="video_id" name="video_id" required>
                            <option value="">Choose Video...</option>
                            <?php foreach ($videos as $video): ?>
                                <option value="<?php echo $video['id']; ?>"><?php echo htmlspecialchars($video['title']); ?> (₹<?php echo number_format($video['price']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="days">Access Duration (Days)</label>
                    <input type="number" id="days" name="days" value="30" min="1" max="365" required>
                </div>
                
                <div style="display: flex; gap: var(--spacing-3); margin-top: var(--spacing-6);">
                    <button type="submit" name="assign_video" class="btn btn-primary">
                        <i class="fas fa-video"></i> Assign Video
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="hideAssignForm()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAssignForm() {
            document.getElementById('assignModal').style.display = 'flex';
        }
        
        function hideAssignForm() {
            document.getElementById('assignModal').style.display = 'none';
        }
    </script>
</body>
</html>