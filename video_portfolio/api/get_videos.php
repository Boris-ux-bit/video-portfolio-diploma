<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

$sql = "SELECT v.*, u.username, u.role,
        DATE_FORMAT(v.created_at, '%d.%m.%Y') as created_date,
        (SELECT COUNT(*) FROM likes WHERE video_id = v.id) as likes_count
        FROM videos v
        JOIN users u ON v.user_id = u.id
        WHERE v.title LIKE :search OR v.description LIKE :search";

if ($sort == 'popular') {
    $sql .= " ORDER BY likes_count DESC";
} else {
    $sql .= " ORDER BY v.created_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute(['search' => "%$search%"]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'videos' => $videos
]);
?>