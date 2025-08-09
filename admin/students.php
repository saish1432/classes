<?php
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    redirect('login.php');
}

$success = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_student'])) {
        $name = sanitize($_POST['name']);
        $image = sanitize($_POST['image']);
        $description = sanitize($_POST['description']);
        
        if ($name && $image && $description) {
            $stmt = $pdo->prepare("INSERT INTO top_students (name, image, description) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $image, $description])) {
                $success = 'Student added successfully!';
            } else {
                $error = 'Failed to add student.';
            }
        } else {
            $error = 'All fields are required.';
        }
    }
    
    if (isset($_POST['update_student'])) {
        $id = (int)$_POST['id'];
        $name = sanitize($_POST['name']);
        $image = sanitize($_POST['image']);
        $description = sanitize($_POST['description']);
        
        if ($id && $name && $image && $description) {
            $stmt = $pdo->prepare("UPDATE top_students SET name = ?, image = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$name, $image, $description, $id])) {
                $success = 'Student updated successfully!';
            } else {
                $error = 'Failed to update student.';
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM top_students WHERE id = ?");
    if ($stmt->execute([$id])) {
        $success = 'Student deleted successfully!';
    } else {
        $error = 'Failed to delete student.';
    }
}

// Get all students
$students = $pdo->query("SELECT * FROM top_students ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Get student for editing
$edit_student = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM top_students WHERE id = ?");
    $stmt->execute([$id]);
    $edit_student = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - GT Online Class</title>
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
        .students-table {
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
        .student-image {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
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
        }
        .modal-content {
            background: white;
            padding: var(--spacing-8);
            border-radius: var(--spacing-4);
            width: 90%;
            max-width: 500px;
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
                <a href="students.php" class="active"><i class="fas fa-users"></i> Students</a>
                <a href="videos.php"><i class="fas fa-video"></i> Videos</a>
                <a href="testimonials.php"><i class="fas fa-comments"></i> Reviews</a>
                <a href="users.php"><i class="fas fa-user-graduate"></i> Users</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container" style="padding: var(--spacing-8) var(--spacing-4);">
        <div class="content-header">
            <h1><i class="fas fa-users"></i> Manage Top Students</h1>
            <button class="btn btn-primary" onclick="showAddForm()">
                <i class="fas fa-plus"></i> Add New Student
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

        <div class="students-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo $student['id']; ?></td>
                            <td>
                                <img src="<?php echo $student['image']; ?>" alt="<?php echo $student['name']; ?>" class="student-image">
                            </td>
                            <td><?php echo $student['name']; ?></td>
                            <td><?php echo substr($student['description'], 0, 50) . '...'; ?></td>
                            <td>
                                <div class="actions">
                                    <a href="?edit=<?php echo $student['id']; ?>" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="?delete=<?php echo $student['id']; ?>" 
                                       class="btn btn-sm" 
                                       style="background: var(--error-color); color: white;"
                                       onclick="return confirm('Are you sure you want to delete this student?')">
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

    <!-- Add/Edit Form Modal -->
    <div id="studentModal" class="form-modal" style="display: <?php echo $edit_student ? 'flex' : 'none'; ?>;">
        <div class="modal-content">
            <h2><?php echo $edit_student ? 'Edit Student' : 'Add New Student'; ?></h2>
            <form method="POST">
                <?php if ($edit_student): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_student['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Student Name</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo $edit_student ? htmlspecialchars($edit_student['name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="image">Image URL</label>
                    <input type="url" id="image" name="image" required 
                           value="<?php echo $edit_student ? htmlspecialchars($edit_student['image']) : ''; ?>"
                           placeholder="https://images.pexels.com/...">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" required rows="3"><?php echo $edit_student ? htmlspecialchars($edit_student['description']) : ''; ?></textarea>
                </div>
                
                <div style="display: flex; gap: var(--spacing-3); margin-top: var(--spacing-6);">
                    <button type="submit" name="<?php echo $edit_student ? 'update_student' : 'add_student'; ?>" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $edit_student ? 'Update' : 'Add'; ?> Student
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="hideForm()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showAddForm() {
            document.getElementById('studentModal').style.display = 'flex';
        }
        
        function hideForm() {
            document.getElementById('studentModal').style.display = 'none';
            window.location.href = 'students.php';
        }
    </script>
</body>
</html>