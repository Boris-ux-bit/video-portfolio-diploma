<?php
require_once '../config/database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$video_id = $_POST['video_id'] ?? 0;
$content = trim($_POST['content'] ?? '');

if ($video_id <= 0) {
    header("Location: ../watch.php?id=$video_id&error=invalid_video");
    exit();
}

if (empty($content)) {
    header("Location: ../watch.php?id=$video_id&error=empty_comment");
    exit();
}

try {
    $insertStmt = $pdo->prepare("INSERT INTO comments (user_id, video_id, content) VALUES (?, ?, ?)");
    $insertStmt->execute([$_SESSION['user_id'], $video_id, $content]);
    header("Location: ../watch.php?id=$video_id&success=comment_added");
    exit();
} catch (PDOException $e) {
    header("Location: ../watch.php?id=$video_id&error=db_error");
    exit();
}
?>