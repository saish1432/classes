<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $name = sanitize($_POST['name'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $review = sanitize($_POST['review'] ?? '');
    
    // Validate required fields
    if (empty($name) || empty($mobile) || empty($review)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    // Validate mobile number
    if (!preg_match('/^[6-9]\d{9}$/', preg_replace('/\D/', '', $mobile))) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid mobile number']);
        exit;
    }
    
    // Validate review length
    if (strlen($review) > 500) {
        echo json_encode(['success' => false, 'message' => 'Review must be maximum 500 characters']);
        exit;
    }
    
    // Insert testimonial
    $stmt = $pdo->prepare("INSERT INTO testimonials (name, mobile, review, status) VALUES (?, ?, ?, 'pending')");
    $stmt->execute([$name, $mobile, $review]);
    
    echo json_encode(['success' => true, 'message' => 'Testimonial submitted successfully']);
    
} catch (Exception $e) {
    error_log("Testimonial submission error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred. Please try again.']);
}
?>