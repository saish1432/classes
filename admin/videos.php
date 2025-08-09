<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_video'])) {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $type = sanitize($_POST['type']);
    $price = floatval($_POST['price']);
    
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        $fileName = time() . '_' . basename($_FILES['video']['name']);
        $uploadPath = $uploadDir . $fileName;
        
        // Check file size (10MB max)
        if ($_FILES['video']['size'] > MAX_FILE_SIZE) {
            $error = 'File size exceeds 10MB limit.';
        } else {
            // Check file type
            $allowedTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv', 'video/flv'];
            if (in_array($_FILES['video']['type'], $allowedTypes)) {
                if (move_uploaded_file($_FILES['video']['tmp_name'], $uploadPath)) {
                    $stmt = $pdo->prepare("INSERT INTO videos (title, description, filename, type, price) VALUES (?, ?, ?, ?, ?)");
                    if ($stmt->execute([$title, $description, $fileName, $type, $price])) {
                        $success = 'Video uploaded successfully!';
                    } else {
                        $error = 'Failed to save video information.';
                        unlink($uploadPath); // Delete uploaded file
                    }
                } else {
                    $error = 'Failed to upload video file.';
                }
            } else {
                $error = 'Invalid file type. Only video files are allowed.';
            }
        }
    } else {
        $error = 'Please select a video file.';
    }
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_video'])) {
    $id = (int)$_POST['id'];
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $type = sanitize($_POST['type']);
    $price = floatval($_POST['price']);
    $status = sanitize($_POST['status']);
    
    $stmt = $pdo->prepare("UPDATE videos SET title = ?, description = ?, type = ?, price = ?, status = ? WHERE id = ?");
    if ($stmt->execute([$title, $description, $type, $price, $status, $id])) {
        $success = 'Video updated successfully!';
    } else {
        $error = 'Failed to update video.';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT filename FROM videos WHERE id = ?");
    $stmt->execute([$id]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($video) {
        $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
        if ($stmt->execute([$id])) {
            // Delete file
            $filePath = '../uploads/' . $video['filename'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $success = 'Video deleted successfully!';
        } else {
            $error = 'Failed to delete video.';
        }
    }
}

// Get all videos
$videos = $pdo->query("SELECT * FROM videos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get video for editing
$edit_video = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->execute([$id]);
    $edit_video = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Management - GT Online Class</title>
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
        .videos-table {
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
        .video-thumbnail {
            width: 80px;
            height: 60px;
            background: var(--gray-200);
            border-radius: var(--spacing-2);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .status-badge {
            padding: var(--spacing-1) var(--spacing-2);
            border-radius: var(--spacing-1);
            font-size: var(--font-size-xs);
            font-weight: 600;
        }
        .status-active { background: var(--success-color); color: white; }
        .status-inactive { background: var(--error-color); color: white; }
        .type-badge {
            padding: var(--spacing-1) var(--spacing-2);
            border-radius: var(--spacing-1);
            font-size: var(--font-size-xs);
            font-weight: 600;
        }
        .type-training { background: var(--primary-color); color: white; }
        .type-recorded { background: var(--accent-color); color: white; }
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
                <a href="videos.php" class="active"><i class="fas fa-video"></i> Videos</a>
                <a href="testimonials.php"><i class="fas fa-comments"></i> Reviews</a>
                <a href="users.php"><i class="fas fa-user-graduate"></i> Users</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding: var(--spacing-8) var(--spacing-4);">
        <div class="content-header">
            <h1><i class="fas fa-video"></i> Video Management</h1>
            <button class="btn btn-primary" onclick="showUploadForm()">
                <i class="fas fa-upload"></i> Upload New Video
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

        <div class="videos-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($videos as $video): ?>
                        <tr>
                            <td><?php echo $video['id']; ?></td>
                            <td>
                                <div class="video-thumbnail">
                                    <i class="fas fa-play" style="color: var(--gray-500);"></i>
                                </div>
                            </td>
                            <td>
                                <strong><?php echo $video['title']; ?></strong><br>
                                <small style="color: var(--gray-600);"><?php echo substr($video['description'], 0, 50) . '...'; ?></small>
                            </td>
                            <td>
                                <span class="type-badge type-<?php echo $video['type']; ?>">
                                    <?php echo ucfirst($video['type']); ?>
                                </span>
                            </td>
                            <td>₹<?php echo number_format($video['price']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $video['status']; ?>">
                                    <?php echo ucfirst($video['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="?edit=<?php echo $video['id']; ?>" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="?delete=<?php echo $video['id']; ?>" 
                                       class="btn btn-sm" 
                                       style="background: var(--error-color); color: white;"
                                       onclick="return confirm('Are you sure you want to delete this video?')">
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

    <!-- Upload/Edit Form Modal -->
    <div id="videoModal" class="form-modal" style="display: <?php echo $edit_video ? 'flex' : 'none'; ?>;">
        <div class="modal-content">
            <h2><?php echo $edit_video ? 'Edit Video' : 'Upload New Video'; ?></h2>
            <form method="POST" enctype="multipart/form-data">
                <?php if ($edit_video): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_video['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">Video Title</label>
                    <input type="text" id="title" name="title" required 
                           value="<?php echo $edit_video ? htmlspecialchars($edit_video['title']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required rows="3"><?php echo $edit_video ? htmlspecialchars($edit_video['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="type">Video Type</label>
                        <select id="type" name="type" required>
                            <option value="training" <?php echo ($edit_video && $edit_video['type'] === 'training') ? 'selected' : ''; ?>>Training (Free)</option>
                            <option value="recorded" <?php echo ($edit_video && $edit_video['type'] === 'recorded') ? 'selected' : ''; ?>>Recorded (Paid)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price (₹)</label>
                        <input type="number" id="price" name="price" min="0" step="0.01" 
                               value="<?php echo $edit_video ? $edit_video['price'] : '99'; ?>">
                    </div>
                </div>
                
                <?php if ($edit_video): ?>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="active" <?php echo ($edit_video['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($edit_video['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                <?php else: ?>
                    <div class="form-group">
                        <label for="video">Video File (Max 10MB)</label>
                        <input type="file" id="video" name="video" accept="video/*" required>
                        <small style="color: var(--gray-600);">Supported formats: MP4, AVI, MOV, WMV, FLV</small>
                    </div>
                <?php endif; ?>
                
                <div style="display: flex; gap: var(--spacing-3); margin-top: var(--spacing-6);">
                    <button type="submit" name="<?php echo $edit_video ? 'update_video' : 'upload_video'; ?>" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $edit_video ? 'Update' : 'Upload'; ?> Video
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="hideForm()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showUploadForm() {
            document.getElementById('videoModal').style.display = 'flex';
        }
        
        function hideForm() {
            document.getElementById('videoModal').style.display = 'none';
            window.location.href = 'videos.php';
        }
        
        // Auto-set price based on type
        document.getElementById('type').addEventListener('change', function() {
            const priceField = document.getElementById('price');
            if (this.value === 'training') {
                priceField.value = '0';
            } else if (this.value === 'recorded') {
                priceField.value = '99';
            }
        });
    </script>
</body>
</html>