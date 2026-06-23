<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit();
}

$video_id = intval($_POST['video_id'] ?? 0);
$score = intval($_POST['score'] ?? 0);

if ($video_id <= 0 || $score < 1 || $score > 5) {
    header("Location: ../watch.php?id=$video_id&error=invalid_grade");
    exit();
}

try {
    $check = $pdo->prepare("SELECT id FROM grades WHERE user_id = ? AND video_id = ?");
    $check->execute([$_SESSION['user_id'], $video_id]);
    
    if ($check->fetch()) {
        $stmt = $pdo->prepare("UPDATE grades SET score = ? WHERE user_id = ? AND video_id = ?");
        $stmt->execute([$score, $_SESSION['user_id'], $video_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO grades (user_id, video_id, score) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $video_id, $score]);
    }
    
    header("Location: ../watch.php?id=$video_id&success=grade_added");
    exit();
} catch (PDOException $e) {
    header("Location: ../watch.php?id=$video_id&error=db_error");
    exit();
}
?>