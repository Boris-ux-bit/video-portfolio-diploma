<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Удаление видео
if (isset($_GET['delete'])) {
    $video_id = $_GET['delete'];
    $check = $pdo->prepare("SELECT video_path FROM videos WHERE id = ? AND user_id = ?");
    $check->execute([$video_id, $_SESSION['user_id']]);
    $video = $check->fetch();
    if ($video) {
        if (file_exists($video['video_path'])) {
            unlink($video['video_path']);
        }
        $pdo->prepare("DELETE FROM videos WHERE id = ?")->execute([$video_id]);
    }
    header('Location: profile.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM videos WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$videos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Мои видео - ВидеоПортфолио</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f5f5;
        }
        .navbar {
            background: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .nav-links a {
            text-decoration: none;
            color: #333;
            margin-left: 1.5rem;
        }
        .container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        h1 {
            color: #333;
        }
        .upload-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            text-decoration: none;
        }
        .video-list {
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }
        .video-item {
            display: flex;
            padding: 1rem;
            border-bottom: 1px solid #eee;
            gap: 1rem;
            align-items: center;
        }
        .video-thumb {
            width: 120px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
        }
        .video-details {
            flex: 1;
        }
        .video-title {
            font-weight: bold;
            margin-bottom: 0.25rem;
        }
        .video-meta {
            font-size: 0.8rem;
            color: #666;
        }
        .video-actions {
            display: flex;
            gap: 0.5rem;
        }
        .btn-sm {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            text-decoration: none;
            font-size: 0.8rem;
        }
        .btn-watch {
            background: #667eea;
            color: white;
        }
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        .no-videos {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        a {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">🎓 ВидеоПортфолио</div>
        <div class="nav-links">
            <a href="index.php">Главная</a>
            <a href="upload.php">📤 Загрузить</a>
            <span>Привет, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="logout.php">Выйти</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Мои видео</h1>
            <a href="upload.php" class="upload-btn">+ Загрузить видео</a>
        </div>

        <div class="video-list">
            <?php if(count($videos) > 0): ?>
                <?php foreach($videos as $video): ?>
                    <div class="video-item">
                        <div class="video-thumb">🎥</div>
                        <div class="video-details">
                            <div class="video-title"><?= htmlspecialchars($video['title']) ?></div>
                            <div class="video-meta">
                                <?= date('d.m.Y', strtotime($video['created_at'])) ?> • 👁️ <?= $video['views'] ?> просмотров
                            </div>
                        </div>
                        <div class="video-actions">
                            <a href="watch.php?id=<?= $video['id'] ?>" class="btn-sm btn-watch">Смотреть</a>
                            <a href="?delete=<?= $video['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Удалить видео?')">Удалить</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-videos">
                    <p>У вас пока нет загруженных видео</p>
                    <a href="upload.php" class="upload-btn" style="display: inline-block; margin-top: 1rem;">Загрузить первое видео</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>