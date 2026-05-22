<?php
header('Content-Type: application/json');
require_once '../config/database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$video_id = $data['video_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Проверяем, есть ли уже лайк
$check = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND video_id = ?");
$check->execute([$user_id, $video_id]);
$liked = $check->fetch();

if ($liked) {
    // Удаляем лайк
    $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND video_id = ?")->execute([$user_id, $video_id]);
    $is_liked = false;
} else {
    // Добавляем лайк
    $pdo->prepare("INSERT INTO likes (user_id, video_id) VALUES (?, ?)")->execute([$user_id, $video_id]);
    $is_liked = true;
}

// Считаем общее количество лайков
$count = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE video_id = ?");
$count->execute([$video_id]);
$likes_count = $count->fetchColumn();

echo json_encode([
    'success' => true,
    'liked' => $is_liked,
    'likes_count' => $likes_count
]);
?>