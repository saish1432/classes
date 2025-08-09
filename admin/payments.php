<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle payment approval
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_payment'])) {
        $payment_id = (int)$_POST['payment_id'];
        
        $stmt = $pdo->prepare("UPDATE assigned_videos SET payment_status = 'completed', status = 'active' WHERE id = ?");
        if ($stmt->execute([$payment_id])) {
            $success = 'Payment approved and video access granted!';
        } else {
            $error = 'Failed to approve payment.';
        }
    }
    
    if (isset($_POST['reject_payment'])) {
        $payment_id = (int)$_POST['payment_id'];
        
        $stmt = $pdo->prepare("DELETE FROM assigned_videos WHERE id = ?");
        if ($stmt->execute([$payment_id])) {
            $success = 'Payment rejected and removed.';
        } else {
            $error = 'Failed to reject payment.';
        }
    }
}

// Get filter
$filter = $_GET['status'] ?? 'all';
$whereClause = '';
if ($filter !== 'all') {
    $whereClause = "WHERE av.payment_status = '" . sanitize($filter) . "'";
}

// Get payments with user and video details
$payments = $pdo->query("
    SELECT av.*, u.name as user_name, u.email, u.mobile, v.title as video_title, v.price
    FROM assigned_videos av
    JOIN users u ON av.user_id = u.id
    JOIN videos v ON av.video_id = v.id
    $whereClause
    ORDER BY av.purchase_date DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$total_earnings = $pdo->query("SELECT COALESCE(SUM(payment_amount), 0) FROM assigned_videos WHERE payment_status = 'completed'")->fetchColumn();
$pending_count = $pdo->query("SELECT COUNT(*) FROM assigned_videos WHERE payment_status = 'pending'")->fetchColumn();
$today_earnings = $pdo->query("SELECT COALESCE(SUM(payment_amount), 0) FROM assigned_videos WHERE payment_status = 'completed' AND DATE(purchase_date) = CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - GT Online Class</title>
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
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-4);
            margin-bottom: var(--spacing-8);
        }
        .stat-card {
            background: white;
            padding: var(--spacing-6);
            border-radius: var(--spacing-3);
            text-align: center;
            box-shadow: var(--shadow);
        }
        .stat-card i {
            font-size: 2rem;
            margin-bottom: var(--spacing-2);
        }
        .stat-number {
            font-size: var(--font-size-2xl);
            font-weight: 700;
            margin-bottom: var(--spacing-1);
        }
        .filter-tabs {
            display: flex;
            gap: var(--spacing-2);
            margin-bottom: var(--spacing-6);
        }
        .filter-tab {
            padding: var(--spacing-2) var(--spacing-4);
            background: white;
            border: 2px solid var(--gray-200);
            border-radius: var(--spacing-2);
            text-decoration: none;
            color: var(--gray-700);
            transition: var(--transition-fast);
        }
        .filter-tab.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        .payments-table {
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
        .status-badge {
            padding: var(--spacing-1) var(--spacing-2);
            border-radius: var(--spacing-1);
            font-size: var(--font-size-xs);
            font-weight: 600;
        }
        .status-pending { background: var(--warning-color); color: white; }
        .status-completed { background: var(--success-color); color: white; }
        .status-failed { background: var(--error-color); color: white; }
        .actions {
            display: flex;
            gap: var(--spacing-2);
        }
        .btn-sm {
            padding: var(--spacing-1) var(--spacing-3);
            font-size: var(--font-size-sm);
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
        @media (max-width: 768px) {
            .admin-menu { display: none; }
            .table { font-size: var(--font-size-sm); }
            .actions { flex-direction: column; }
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
                <a href="payments.php" class="active"><i class="fas fa-rupee-sign"></i> Payments</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding: var(--spacing-8) var(--spacing-4);">
        <h1 style="margin-bottom: var(--spacing-8); color: var(--gray-800);">
            <i class="fas fa-rupee-sign"></i> Payment Management
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

        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-card">
                <i class="fas fa-rupee-sign" style="color: var(--success-color);"></i>
                <div class="stat-number" style="color: var(--success-color);">₹<?php echo number_format($total_earnings); ?></div>
                <div class="stat-label">Total Earnings</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock" style="color: var(--warning-color);"></i>
                <div class="stat-number" style="color: var(--warning-color);"><?php echo $pending_count; ?></div>
                <div class="stat-label">Pending Payments</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-day" style="color: var(--primary-color);"></i>
                <div class="stat-number" style="color: var(--primary-color);">₹<?php echo number_format($today_earnings); ?></div>
                <div class="stat-label">Today's Earnings</div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="?status=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> All Payments
            </a>
            <a href="?status=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Pending
            </a>
            <a href="?status=completed" class="filter-tab <?php echo $filter === 'completed' ? 'active' : ''; ?>">
                <i class="fas fa-check"></i> Completed
            </a>
        </div>

        <!-- Payments Table -->
        <div class="payments-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Video</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td>#<?php echo $payment['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($payment['user_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($payment['email']); ?></small><br>
                                <small><?php echo htmlspecialchars($payment['mobile']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($payment['video_title']); ?></td>
                            <td>₹<?php echo number_format($payment['payment_amount']); ?></td>
                            <td><?php echo date('M j, Y H:i', strtotime($payment['purchase_date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $payment['payment_status']; ?>">
                                    <?php echo ucfirst($payment['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <?php if ($payment['payment_status'] === 'pending'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                            <button type="submit" name="approve_payment" class="btn btn-sm" style="background: var(--success-color); color: white;">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                            <button type="submit" name="reject_payment" class="btn btn-sm" style="background: var(--error-color); color: white;" onclick="return confirm('Are you sure you want to reject this payment?')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: var(--gray-500); font-size: var(--font-size-sm);">
                                            <?php echo $payment['payment_status'] === 'completed' ? 'Approved' : 'No actions'; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (empty($payments)): ?>
                <div style="text-align: center; padding: var(--spacing-12);">
                    <i class="fas fa-receipt" style="font-size: 4rem; color: var(--gray-300); margin-bottom: var(--spacing-4);"></i>
                    <h3 style="color: var(--gray-600);">No payments found</h3>
                    <p style="color: var(--gray-500);">No payments match the current filter.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>