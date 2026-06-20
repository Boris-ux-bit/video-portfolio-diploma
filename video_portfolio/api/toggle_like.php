<?php
require_once '../config/database.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$video_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

if ($video_id <= 0) {
    header("Location: ../watch.php?id=$video_id&error=invalid_video");
    exit();
}


$checkStmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND video_id = ?");
$checkStmt->execute([$user_id, $video_id]);
$existingLike = $checkStmt->fetch();

if ($existingLike) {
   
    $deleteStmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND video_id = ?");
    $deleteStmt->execute([$user_id, $video_id]);
} else {
    
    $insertStmt = $pdo->prepare("INSERT INTO likes (user_id, video_id) VALUES (?, ?)");
    $insertStmt->execute([$user_id, $video_id]);
}


header("Location: ../watch.php?id=$video_id");
exit();
?>