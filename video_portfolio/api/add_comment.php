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
$content = trim($data['content'] ?? '');

if (empty($content)) {
    echo json_encode(['success' => false, 'error' => 'Комментарий не может быть пустым']);
    exit();
}

$stmt = $pdo->prepare("INSERT INTO comments (user_id, video_id, content) VALUES (?, ?, ?)");
$stmt->execute([$_SESSION['user_id'], $video_id, $content]);

echo json_encode([
    'success' => true,
    'username' => $_SESSION['username']
]);
?>