<?php 
require_once 'config.php';

// Get settings
$stmt = $pdo->query("SELECT * FROM settings");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Get top students
$top_students = $pdo->query("SELECT * FROM top_students ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

// Get training videos
$training_videos = $pdo->query("SELECT * FROM videos WHERE type = 'training' AND status = 'active' ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Get recorded lectures
$recorded_lectures = $pdo->query("SELECT * FROM videos WHERE type = 'recorded' AND status = 'active' ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Check if user is logged in to show video access
$user_videos = [];
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("
        SELECT v.id 
        FROM videos v 
        JOIN assigned_videos av ON v.id = av.video_id 
        WHERE av.user_id = ? AND av.status = 'active' AND av.expiry_date > NOW()
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_videos = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'id');
}

// Get approved testimonials
$testimonials = $pdo->query("SELECT * FROM testimonials WHERE status = 'approved' ORDER BY id DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $settings['site_title'] ?? 'GT Online Class'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- WhatsApp Help Button -->
    <a href="https://wa.me/<?php echo $settings['whatsapp_number']; ?>?text=Hello, I need help regarding GT Online Class" 
       class="whatsapp-float" target="_blank" title="Need Help? Chat with us">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-graduation-cap"></i>
                <span>GT Online Class</span>
            </div>
            <div class="nav-links">
                <a href="#home">Home</a>
                <a href="#students">Top Students</a>
                <a href="#trainings">Trainings</a>
                <a href="#admission">Admission</a>
                <a href="#testimonials">Reviews</a>
                <a href="#lectures">Lectures</a>
                <a href="user/login.php" class="btn btn-primary">Login</a>
            </div>
            <div class="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Welcome Section -->
    <section id="home" class="hero">
        <div class="hero-background">
            <div class="floating-icons">
                <i class="fas fa-chalkboard-teacher" title="Online Classes"></i>
                <i class="fas fa-users" title="Students"></i>
                <i class="fas fa-laptop" title="E-Learning"></i>
                <i class="fas fa-graduation-cap" title="Education"></i>
                <i class="fas fa-book-reader" title="Study"></i>
                <i class="fas fa-video" title="Video Lectures"></i>
                <i class="fas fa-certificate" title="Certification"></i>
                <i class="fas fa-brain" title="Learning"></i>
            </div>
        </div>
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title animate-fade-up"><?php echo $settings['welcome_message']; ?></h1>
                <p class="hero-subtitle animate-fade-up" style="animation-delay: 0.2s">
                    Empowering minds through quality education and innovative learning experiences
                </p>
                <div class="hero-buttons animate-fade-up" style="animation-delay: 0.4s">
                    <a href="#trainings" class="btn btn-primary">Start Learning</a>
                    <a href="#admission" class="btn btn-secondary">Join Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Top 10 Students Section -->
    <section id="students" class="students-section">
        <div class="container">
            <h2 class="section-title">
                <i class="fas fa-trophy"></i>
                TOP 10 Students of the Year
            </h2>
            <div class="students-grid">
                <?php foreach ($top_students as $student): ?>
                    <div class="student-card animate-scale">
                        <div class="student-image">
                            <img src="<?php echo $student['image']; ?>" alt="<?php echo $student['name']; ?>">
                            <div class="student-overlay">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <h3><?php echo $student['name']; ?></h3>
                        <p><?php echo $student['description']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Training Videos Section -->
    <section id="trainings" class="trainings-section">
        <div class="container">
            <h2 class="section-title">
                <i class="fas fa-play-circle"></i>
                New Launched Trainings & Activities
            </h2>
            <div class="videos-grid">
                <?php foreach ($training_videos as $video): ?>
                    <div class="video-card animate-fade-up">
                        <div class="video-thumbnail">
                            <video controls>
                                <source src="uploads/<?php echo $video['filename']; ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div class="video-overlay">
                                <i class="fas fa-play"></i>
                            </div>
                        </div>
                        <div class="video-info">
                            <h3><?php echo $video['title']; ?></h3>
                            <p><?php echo $video['description']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Admission Form Section -->
    <section id="admission" class="admission-section">
        <div class="container">
            <h2 class="section-title">
                <i class="fas fa-user-plus"></i>
                Admission Form - Next Offline Batch
            </h2>
            <div class="form-container">
                <form id="admissionForm" class="admission-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fullName"><i class="fas fa-user"></i> Full Name</label>
                            <input type="text" id="fullName" name="fullName" required>
                        </div>
                        <div class="form-group">
                            <label for="mobile"><i class="fas fa-phone"></i> Mobile Number</label>
                            <input type="tel" id="mobile" name="mobile" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="address"><i class="fas fa-map-marker-alt"></i> Address</label>
                            <input type="text" id="address" name="address" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="schoolName"><i class="fas fa-school"></i> School Name</label>
                            <input type="text" id="schoolName" name="schoolName" required>
                        </div>
                        <div class="form-group">
                            <label for="standard"><i class="fas fa-graduation-cap"></i> Standard</label>
                            <input type="text" id="standard" name="standard" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="timeSlot"><i class="fas fa-clock"></i> Time Slot (Optional)</label>
                        <input type="text" id="timeSlot" name="timeSlot" placeholder="Leave blank if no preference">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fab fa-whatsapp"></i>
                        Submit via WhatsApp
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials-section">
        <div class="container">
            <h2 class="section-title">
                <i class="fas fa-star"></i>
                Student Reviews & Ratings
            </h2>
            <div class="testimonials-grid">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="testimonial-card animate-fade-up">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="testimonial-info">
                                <h4><?php echo $testimonial['name']; ?></h4>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <p class="testimonial-text"><?php echo substr($testimonial['review'], 0, 200) . '...'; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="testimonial-actions">
                <button class="btn btn-primary" onclick="openTestimonialForm()">
                    <i class="fas fa-edit"></i>
                    Write a Testimonial
                </button>
            </div>
        </div>
    </section>

    <!-- Recorded Lectures Section -->
    <section id="lectures" class="lectures-section">
        <div class="container">
            <h2 class="section-title">
                <i class="fas fa-video"></i>
                Recorded Lecture Videos
            </h2>
            <div class="lectures-grid">
                <?php foreach ($recorded_lectures as $lecture): ?>
                    <?php $hasAccess = in_array($lecture['id'], $user_videos); ?>
                    <div class="lecture-card animate-fade-up">
                        <div class="lecture-thumbnail">
                            <?php if (!$hasAccess): ?>
                                <div class="locked-overlay">
                                    <i class="fas fa-lock"></i>
                                    <span>Premium Content</span>
                                </div>
                            <?php endif; ?>
                            <img src="https://images.pexels.com/photos/5905709/pexels-photo-5905709.jpeg?auto=compress&cs=tinysrgb&w=400&h=250&fit=crop" alt="<?php echo $lecture['title']; ?>">
                        </div>
                        <div class="lecture-info">
                            <h3><?php echo $lecture['title']; ?></h3>
                            <p><?php echo $lecture['description']; ?></p>
                            <div class="lecture-price">
                                <span class="price">₹<?php echo number_format($lecture['price']); ?></span>
                                <?php if ($hasAccess): ?>
                                    <a href="user/dashboard.php" class="btn" style="background: var(--success-color); color: white;">
                                        <i class="fas fa-play"></i> Watch Now
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-unlock" onclick="unlockVideo(<?php echo $lecture['id']; ?>)">
                                        <i class="fas fa-unlock"></i>
                                        Unlock @ ₹<?php echo number_format($lecture['price']); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <i class="fas fa-graduation-cap"></i>
                    <span>GT Online Class</span>
                </div>
                <div class="footer-links">
                    <a href="#home">Home</a>
                    <a href="#students">Students</a>
                    <a href="#trainings">Trainings</a>
                    <a href="#admission">Admission</a>
                    <a href="admin/login.php">Admin</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> All Rights Reserved - GTAi.in</p>
            </div>
        </div>
    </footer>

    <!-- Testimonial Modal -->
    <div id="testimonialModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Write Your Testimonial</h2>
            <form id="testimonialForm">
                <div class="form-group">
                    <label for="testimonialName">Your Name</label>
                    <input type="text" id="testimonialName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="testimonialMobile">Mobile Number</label>
                    <input type="tel" id="testimonialMobile" name="mobile" required>
                </div>
                <div class="form-group">
                    <label for="testimonialReview">Your Review (Max 500 words)</label>
                    <textarea id="testimonialReview" name="review" maxlength="500" rows="8" placeholder="Share your experience with GT Online Class..." required></textarea>
                    <div class="character-count">0/500</div>
                </div>
                <button type="submit" class="btn btn-primary">Submit Review</button>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>