<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $id = (int)$_POST['id'];
        $status = sanitize($_POST['status']);
        
        $stmt = $pdo->prepare("UPDATE testimonials SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $id])) {
            $success = 'Testimonial status updated successfully!';
        } else {
            $error = 'Failed to update testimonial status.';
        }
    }
    
    if (isset($_POST['update_testimonial'])) {
        $id = (int)$_POST['id'];
        $name = sanitize($_POST['name']);
        $mobile = sanitize($_POST['mobile']);
        $review = sanitize($_POST['review']);
        $status = sanitize($_POST['status']);
        
        $stmt = $pdo->prepare("UPDATE testimonials SET name = ?, mobile = ?, review = ?, status = ? WHERE id = ?");
        if ($stmt->execute([$name, $mobile, $review, $status, $id])) {
            $success = 'Testimonial updated successfully!';
        } else {
            $error = 'Failed to update testimonial.';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = 'Testimonial deleted successfully!';
    } else {
        $error = 'Failed to delete testimonial.';
    }
}

// Get filter
$filter = $_GET['status'] ?? 'all';
$whereClause = '';
if ($filter !== 'all') {
    $whereClause = "WHERE status = '" . sanitize($filter) . "'";
}

// Get all testimonials
$testimonials = $pdo->query("SELECT * FROM testimonials $whereClause ORDER BY submitted_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get testimonial for editing
$edit_testimonial = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $edit_testimonial = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testimonials Management - GT Online Class</title>
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
        .testimonials-grid {
            display: grid;
            gap: var(--spacing-6);
        }
        .testimonial-card {
            background: white;
            padding: var(--spacing-6);
            border-radius: var(--spacing-4);
            box-shadow: var(--shadow);
        }
        .testimonial-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: var(--spacing-4);
        }
        .testimonial-info h4 {
            margin-bottom: var(--spacing-1);
        }
        .testimonial-meta {
            font-size: var(--font-size-sm);
            color: var(--gray-600);
        }
        .status-badge {
            padding: var(--spacing-1) var(--spacing-2);
            border-radius: var(--spacing-1);
            font-size: var(--font-size-xs);
            font-weight: 600;
        }
        .status-pending { background: var(--warning-color); color: white; }
        .status-approved { background: var(--success-color); color: white; }
        .status-rejected { background: var(--error-color); color: white; }
        .testimonial-text {
            margin-bottom: var(--spacing-4);
            line-height: 1.6;
        }
        .testimonial-actions {
            display: flex;
            gap: var(--spacing-2);
            flex-wrap: wrap;
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
            .content-header { flex-direction: column; gap: var(--spacing-4); }
            .testimonial-actions { flex-direction: column; }
            .filter-tabs { flex-wrap: wrap; }
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
                <a href="testimonials.php" class="active"><i class="fas fa-comments"></i> Reviews</a>
                <a href="users.php"><i class="fas fa-user-graduate"></i> Users</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding: var(--spacing-8) var(--spacing-4);">
        <div class="content-header">
            <h1><i class="fas fa-comments"></i> Testimonials Management</h1>
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

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="?status=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> All
            </a>
            <a href="?status=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Pending
            </a>
            <a href="?status=approved" class="filter-tab <?php echo $filter === 'approved' ? 'active' : ''; ?>">
                <i class="fas fa-check"></i> Approved
            </a>
            <a href="?status=rejected" class="filter-tab <?php echo $filter === 'rejected' ? 'active' : ''; ?>">
                <i class="fas fa-times"></i> Rejected
            </a>
        </div>

        <div class="testimonials-grid">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-info">
                            <h4><?php echo htmlspecialchars($testimonial['name']); ?></h4>
                            <div class="testimonial-meta">
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($testimonial['mobile']); ?> |
                                <i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($testimonial['submitted_at'])); ?>
                            </div>
                        </div>
                        <span class="status-badge status-<?php echo $testimonial['status']; ?>">
                            <?php echo ucfirst($testimonial['status']); ?>
                        </span>
                    </div>
                    
                    <div class="testimonial-text">
                        <?php echo nl2br(htmlspecialchars($testimonial['review'])); ?>
                    </div>
                    
                    <div class="testimonial-actions">
                        <?php if ($testimonial['status'] === 'pending'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $testimonial['id']; ?>">
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" name="update_status" class="btn btn-sm" style="background: var(--success-color); color: white;">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $testimonial['id']; ?>">
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" name="update_status" class="btn btn-sm" style="background: var(--error-color); color: white;">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <a href="?edit=<?php echo $testimonial['id']; ?>" class="btn btn-secondary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        
                        <a href="?delete=<?php echo $testimonial['id']; ?>" 
                           class="btn btn-sm" 
                           style="background: var(--error-color); color: white;"
                           onclick="return confirm('Are you sure you want to delete this testimonial?')">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($testimonials)): ?>
                <div class="testimonial-card" style="text-align: center; padding: var(--spacing-12);">
                    <i class="fas fa-comments" style="font-size: 4rem; color: var(--gray-300); margin-bottom: var(--spacing-4);"></i>
                    <h3 style="color: var(--gray-600);">No testimonials found</h3>
                    <p style="color: var(--gray-500);">No testimonials match the current filter.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Form Modal -->
    <?php if ($edit_testimonial): ?>
    <div id="testimonialModal" class="form-modal" style="display: flex;">
        <div class="modal-content">
            <h2>Edit Testimonial</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $edit_testimonial['id']; ?>">
                
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($edit_testimonial['name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="mobile">Mobile</label>
                    <input type="tel" id="mobile" name="mobile" required 
                           value="<?php echo htmlspecialchars($edit_testimonial['mobile']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="review">Review</label>
                    <textarea id="review" name="review" required rows="6"><?php echo htmlspecialchars($edit_testimonial['review']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="pending" <?php echo ($edit_testimonial['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo ($edit_testimonial['status'] === 'approved') ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo ($edit_testimonial['status'] === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: var(--spacing-3); margin-top: var(--spacing-6);">
                    <button type="submit" name="update_testimonial" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Testimonial
                    </button>
                    <a href="testimonials.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>