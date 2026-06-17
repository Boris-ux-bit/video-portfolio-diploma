<?php
header('Content-Type: application/json');
require_once '../config/database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$video_id = $input['video_id'] ?? 0;
$user_id = (int)$_SESSION['user_id'];

if ($video_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID видео']);
    exit();
}

$checkStmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND video_id = ?");
$checkStmt->execute([$user_id, $video_id]);
$existingLike = $checkStmt->fetch();

if ($existingLike) {
    $deleteStmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND video_id = ?");
    $deleteStmt->execute([$user_id, $video_id]);
    $liked = false;
} else {
    $insertStmt = $pdo->prepare("INSERT INTO likes (user_id, video_id) VALUES (?, ?)");
    $insertStmt->execute([$user_id, $video_id]);
    $liked = true;
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE video_id = ?");
$countStmt->execute([$video_id]);
$likesCount = (int)$countStmt->fetchColumn();

echo json_encode([
    'success' => true,
    'liked' => $liked,
    'likes_count' => $likesCount
]);
?>