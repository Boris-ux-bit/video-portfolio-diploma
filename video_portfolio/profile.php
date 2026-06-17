<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои видео - ВидеоПортфолио</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255,255,255,0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.06) 0%, transparent 40%),
                radial-gradient(circle at 50% 80%, rgba(255,255,255,0.04) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                repeating-linear-gradient(45deg, rgba(255,255,255,0.03) 0px, rgba(255,255,255,0.03) 2px, transparent 2px, transparent 8px);
            pointer-events: none;
            z-index: 0;
        }
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: floatShape 20s infinite ease-in-out;
        }
        .bg-shape:nth-child(1) {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            top: -100px;
            right: -100px;
            animation-delay: 0s;
        }
        .bg-shape:nth-child(2) {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            bottom: -50px;
            left: -50px;
            animation-delay: -5s;
        }
        .bg-shape:nth-child(3) {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -10s;
        }
        .bg-shape:nth-child(4) {
            width: 150px;
            height: 150px;
            background: linear-gradient(135deg, #fccb90 0%, #d57eeb 100%);
            bottom: 30%;
            right: 10%;
            animation-delay: -3s;
            animation-duration: 25s;
        }
        .bg-shape:nth-child(5) {
            width: 250px;
            height: 250px;
            background: linear-gradient(135deg, #89f7fe 0%, #66a6ff 100%);
            top: 20%;
            left: 10%;
            animation-delay: -7s;
            animation-duration: 30s;
        }
        @keyframes floatShape {
            0%, 100% { transform: translate(0, 0) rotate(0deg) scale(1); }
            25% { transform: translate(30px, -30px) rotate(90deg) scale(1.05); }
            50% { transform: translate(-20px, 40px) rotate(180deg) scale(0.95); }
            75% { transform: translate(20px, -20px) rotate(270deg) scale(1.02); }
        }
        .navbar {
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 2px 30px rgba(0,0,0,0.15);
            padding: 0.8rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            position: relative;
            z-index: 10;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .logo {
            font-size: 1.4rem;
            font-weight: 800;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            letter-spacing: -0.5px;
        }
        .logo span {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .nav-links {
            display: flex;
            gap: 0.8rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .nav-links a {
            text-decoration: none;
            color: rgba(255,255,255,0.8);
            transition: all 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 30px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .nav-links a:hover {
            color: white;
            background: rgba(255,255,255,0.15);
            transform: translateY(-2px);
        }
        .nav-links .user-link {
            color: white;
            font-weight: 600;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 30px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
        }
        .nav-links .user-link:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-2px);
        }
        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 30px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102,126,234,0.4);
        }
        .btn-primary:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 30px rgba(102,126,234,0.5);
        }
        .btn-outline {
            border: 2px solid rgba(255,255,255,0.5);
            color: white;
            background: transparent;
        }
        .btn-outline:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
            border-color: white;
        }
        .btn-logout {
            border: 2px solid rgba(255,255,255,0.3);
            color: rgba(255,255,255,0.8);
            background: transparent;
        }
        .btn-logout:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.6);
            color: white;
            transform: translateY(-2px);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 5;
        }
        .profile-header {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2.5rem;
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.3s;
            margin-bottom: 2rem;
        }
        .profile-header:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.12);
        }
        .profile-header .profile-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .profile-header .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 8px 30px rgba(102,126,234,0.3);
            flex-shrink: 0;
        }
        .profile-header .profile-text {
            flex: 1;
        }
        .profile-header h1 {
            font-size: 1.8rem;
            color: white;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }
        .profile-header .subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 1rem;
            line-height: 1.5;
        }
        .profile-header .actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .btn-upload {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            box-shadow: 0 4px 15px rgba(102,126,234,0.3);
        }
        .btn-upload:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 30px rgba(102,126,234,0.4);
        }
        .btn-more {
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            background: transparent;
            padding: 0.75rem 2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
            cursor: pointer;
            font-size: 0.95rem;
        }
        .btn-more:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.5);
            transform: translateY(-2px);
        }
        .videos-section {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2rem;
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.3s;
        }
        .videos-section:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.12);
        }
        .videos-section .section-title {
            font-size: 1.3rem;
            color: white;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .videos-section .section-title .count {
            background: rgba(255,255,255,0.15);
            padding: 0.2rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            color: rgba(255,255,255,0.7);
        }
        .video-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        .video-item {
            background: rgba(255,255,255,0.08);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255,255,255,0.06);
            backdrop-filter: blur(10px);
        }
        .video-item:hover {
            transform: translateY(-6px);
            background: rgba(255,255,255,0.14);
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            border-color: rgba(255,255,255,0.15);
        }
        .video-item-thumb {
            height: 150px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            position: relative;
        }
        .video-item-thumb .thumb-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, transparent 50%, rgba(0,0,0,0.3) 100%);
        }
        .video-item-thumb .views-badge {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            color: white;
            padding: 3px 12px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 500;
        }
        .video-item-body {
            padding: 1.2rem;
        }
        .video-item-body h3 {
            font-size: 1rem;
            color: white;
            font-weight: 600;
            margin-bottom: 0.3rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .video-item-body .meta {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.5);
            margin-bottom: 0.75rem;
        }
        .video-item-body .item-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .btn-sm {
            padding: 0.4rem 1.2rem;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.75rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-watch {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .btn-watch:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(102,126,234,0.3);
        }
        .btn-delete {
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.7);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .btn-delete:hover {
            background: rgba(231, 76, 60, 0.3);
            color: #ff6b6b;
            border-color: rgba(231, 76, 60, 0.3);
            transform: scale(1.05);
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        .empty-state .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            display: block;
        }
        .empty-state h3 {
            font-size: 1.5rem;
            color: white;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .empty-state p {
            color: rgba(255,255,255,0.6);
            font-size: 1rem;
            margin-bottom: 1.5rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        .empty-state .btn-upload {
            display: inline-block;
        }
        .footer {
            text-align: center;
            color: rgba(255,255,255,0.4);
            padding: 2rem 0 1rem;
            font-size: 0.85rem;
            margin-top: 1rem;
        }
        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 1rem; padding: 1rem; }
            .nav-links { justify-content: center; gap: 0.5rem; }
            .nav-links a { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
            .video-list { grid-template-columns: 1fr; }
            .profile-header { padding: 1.5rem; }
            .profile-header .profile-info { flex-direction: column; text-align: center; }
            .profile-header h1 { font-size: 1.4rem; }
            .profile-header .actions { justify-content: center; }
            .profile-header .actions .btn-upload,
            .profile-header .actions .btn-more { width: 100%; text-align: center; }
            .container { padding: 1rem; }
            .bg-shape { display: none; }
            .videos-section { padding: 1.5rem; }
        }
        @media (max-width: 480px) {
            .logo { font-size: 1.1rem; }
            .profile-header h1 { font-size: 1.2rem; }
            .video-item { border-radius: 12px; }
        }
    </style>
</head>
<body>

    <div class="bg-shapes">
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
    </div>

    <nav class="navbar">
        <div class="logo"> <span>ВидеоПортфолио</span></div>
        <div class="nav-links">
            <a href="index.php">Главная</a>
            <a href="profile.php">Мои видео</a>
            <a href="upload.php"> Загрузить</a>
            <a href="account.php" class="user-link"> <?= htmlspecialchars($_SESSION['username']) ?></a>
            <a href="logout.php" class="btn btn-logout">Выйти</a>
        </div>
    </nav>

    <div class="container">
        <div class="profile-header">
            <div class="profile-info">
                <div class="avatar">👤</div>
                <div class="profile-text">
                    <h1> Мои видео</h1>
                    <p class="subtitle">Здесь собраны все ваши загруженные видео.<br>Загружайте новые проекты и делитесь ими с миром!</p>
                </div>
            </div>
            <div class="actions">
                <a href="upload.php" class="btn-upload">➕ Загрузить видео</a>
                <a href="index.php" class="btn-more">Подробнее</a>
            </div>
        </div>

        <div class="videos-section">
            <div class="section-title">
                 Загруженные видео
                <span class="count"><?= count($videos) ?></span>
            </div>

            <?php if(count($videos) > 0): ?>
                <div class="video-list">
                    <?php foreach($videos as $video): ?>
                        <div class="video-item">
                            <div class="video-item-thumb">
                                
                                <div class="thumb-overlay"></div>
                                <span class="views-badge">👁️ <?= $video['views'] ?></span>
                            </div>
                            <div class="video-item-body">
                                <h3><?= htmlspecialchars($video['title']) ?></h3>
                                <div class="meta">
                                     <?= date('d.m.Y', strtotime($video['created_at'])) ?>
                                </div>
                                <div class="item-actions">
                                    <a href="watch.php?id=<?= $video['id'] ?>" class="btn-sm btn-watch">▶ Смотреть</a>
                                    <a href="?delete=<?= $video['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Удалить видео?')">🗑 Удалить</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <span class="icon"></span>
                    <h3>У вас пока нет загруженных видео</h3>
                    <p>Загрузите своё первое видео и начните создавать своё портфолио уже сегодня!</p>
                    <a href="upload.php" class="btn-upload"> Загрузить первое видео</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer">
            © 2024 ВидеоПортфолио. Все права защищены.
        </div>
    </div>

</body>
</html>