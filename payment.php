<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$video_id = (int)($_POST['video_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if (!$video_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid video ID']);
    exit;
}

// Get video details
$stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ? AND type = 'recorded' AND status = 'active'");
$stmt->execute([$video_id]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
    echo json_encode(['success' => false, 'message' => 'Video not found']);
    exit;
}

// Check if user already has access
$stmt = $pdo->prepare("SELECT id FROM assigned_videos WHERE user_id = ? AND video_id = ? AND status = 'active' AND expiry_date > NOW()");
$stmt->execute([$user_id, $video_id]);

if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You already have access to this video']);
    exit;
}

// Get settings
$stmt = $pdo->query("SELECT * FROM settings");
$settings = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$upi_id = $settings['upi_id'] ?? 'admin@paytm';
$whatsapp_number = $settings['whatsapp_number'] ?? '+919876543210';

// Create pending payment record
$stmt = $pdo->prepare("INSERT INTO assigned_videos (user_id, video_id, expiry_date, status, payment_status, payment_amount) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 DAY), 'pending', 'pending', ?)");
$stmt->execute([$user_id, $video_id, $video['price']]);
$payment_id = $pdo->lastInsertId();

// Generate UPI payment URL
$upi_url = "upi://pay?pa={$upi_id}&pn=GT Online Class&am={$video['price']}&cu=INR&tn=Video Payment - {$video['title']}";

echo json_encode([
    'success' => true,
    'payment_id' => $payment_id,
    'video_title' => $video['title'],
    'amount' => $video['price'],
    'upi_id' => $upi_id,
    'upi_url' => $upi_url,
    'whatsapp_number' => $whatsapp_number
]);
?>