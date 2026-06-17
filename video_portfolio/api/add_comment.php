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
$content = trim($input['content'] ?? '');

if ($video_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Неверный ID видео']);
    exit();
}

if (empty($content)) {
    echo json_encode(['success' => false, 'error' => 'Комментарий не может быть пустым']);
    exit();
}

$insertStmt = $pdo->prepare("INSERT INTO comments (user_id, video_id, content) VALUES (?, ?, ?)");
$insertStmt->execute([$_SESSION['user_id'], $video_id, $content]);

echo json_encode([
    'success' => true,
    'username' => htmlspecialchars($_SESSION['username'])
]);
?>