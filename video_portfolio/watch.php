<?php
require_once 'config/database.php';

$video_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT v.*, u.username, u.role FROM videos v JOIN users u ON v.user_id = u.id WHERE v.id = ?");
$stmt->execute([$video_id]);
$video = $stmt->fetch();

if (!$video) {
    die('Видео не найдено');
}

$pdo->prepare("UPDATE videos SET views = views + 1 WHERE id = ?")->execute([$video_id]);

$comments = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.video_id = ? ORDER BY c.created_at DESC");
$comments->execute([$video_id]);
$comments = $comments->fetchAll();

$user_liked = false;
if (isset($_SESSION['user_id'])) {
    $like_check = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND video_id = ?");
    $like_check->execute([$_SESSION['user_id'], $video_id]);
    $user_liked = $like_check->fetch();
}

$likes_count = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE video_id = ?");
$likes_count->execute([$video_id]);
$likes_count = $likes_count->fetchColumn();

if (isset($_GET['delete_comment']) && isset($_SESSION['user_id'])) {
    $comment_id = $_GET['delete_comment'];
    $check = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
    $check->execute([$comment_id]);
    $comment = $check->fetch();
    if ($comment && ($_SESSION['user_id'] == $comment['user_id'] || $_SESSION['role'] == 'admin')) {
        $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$comment_id]);
    }
    header("Location: watch.php?id=$video_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($video['title']) ?> - ВидеоПортфолио</title>
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
        .nav-links .user-name {
            color: white;
            font-weight: 600;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 30px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s;
        }
        .nav-links .user-name:hover {
            background: rgba(255,255,255,0.2);
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
        .back-link {
            display: inline-block;
            margin-bottom: 1.5rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        .back-link:hover {
            color: white;
            transform: translateX(-3px);
        }
        .video-container {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.08);
            margin-bottom: 2rem;
            transition: all 0.3s;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }
        .video-container:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.12);
        }
        .video-wrapper {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            background: #000;
        }
        .video-wrapper video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: block;
        }
        .video-info {
            padding: 2rem;
        }
        .video-title {
            font-size: 1.8rem;
            color: white;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .video-meta {
            color: rgba(255,255,255,0.6);
            font-size: 0.9rem;
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .video-meta span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .video-meta .author {
            color: rgba(255,255,255,0.9);
            font-weight: 600;
        }
        .video-description {
            color: rgba(255,255,255,0.7);
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 1.5rem;
        }
        .like-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        .like-btn {
            background: rgba(255,255,255,0.1);
            border: none;
            padding: 0.7rem 2rem;
            border-radius: 30px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
            color: rgba(255,255,255,0.8);
            font-weight: 600;
            font-family: 'Inter', sans-serif;
        }
        .like-btn:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.02);
        }
        .like-btn.liked {
            background: rgba(231, 76, 60, 0.3);
            color: #ff6b6b;
        }
        .like-btn.liked:hover {
            background: rgba(231, 76, 60, 0.4);
        }
        .like-count {
            color: rgba(255,255,255,0.8);
            font-size: 1rem;
            font-weight: 500;
        }
        .comments-section {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2rem;
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.3s;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }
        .comments-section:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.12);
        }
        .comments-title {
            font-size: 1.3rem;
            color: white;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        .comments-title .count {
            background: rgba(255,255,255,0.15);
            padding: 0.2rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            color: rgba(255,255,255,0.7);
        }
        .comment-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .comment-input {
            flex: 1;
            padding: 0.85rem 1.2rem;
            border: 2px solid rgba(255,255,255,0.12);
            border-radius: 16px;
            font-size: 1rem;
            transition: all 0.3s;
            background: rgba(255,255,255,0.06);
            color: white;
            font-family: 'Inter', sans-serif;
            min-width: 200px;
        }
        .comment-input::placeholder {
            color: rgba(255,255,255,0.35);
        }
        .comment-input:focus {
            outline: none;
            border-color: rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            box-shadow: 0 0 0 4px rgba(255,255,255,0.05);
        }
        .comment-btn {
            padding: 0.85rem 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            white-space: nowrap;
            box-shadow: 0 4px 15px rgba(102,126,234,0.3);
        }
        .comment-btn:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 30px rgba(102,126,234,0.4);
        }
        .comment-btn:active {
            transform: scale(0.98);
        }
        .comments-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .comment {
            background: rgba(255,255,255,0.06);
            border-radius: 16px;
            padding: 1rem 1.2rem;
            border: 1px solid rgba(255,255,255,0.06);
            transition: all 0.3s;
        }
        .comment:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.1);
        }
        .comment .comment-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-bottom: 0.4rem;
        }
        .comment .comment-author {
            color: rgba(255,255,255,0.9);
            font-weight: 600;
            font-size: 0.95rem;
        }
        .comment .comment-date {
            color: rgba(255,255,255,0.4);
            font-size: 0.8rem;
        }
        .comment .comment-text {
            color: rgba(255,255,255,0.75);
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .comment .delete-comment {
            color: rgba(255,255,255,0.3);
            text-decoration: none;
            font-size: 0.75rem;
            transition: all 0.3s;
            margin-left: auto;
        }
        .comment .delete-comment:hover {
            color: #ff6b6b;
        }
        .no-comments {
            color: rgba(255,255,255,0.5);
            text-align: center;
            padding: 2rem;
            font-size: 0.95rem;
        }
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(15px);
            color: white;
            padding: 14px 28px;
            border-radius: 16px;
            z-index: 1000;
            animation: slideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            font-weight: 500;
        }
        .toast.success {
            border-left: 4px solid #27ae60;
        }
        .toast.error {
            border-left: 4px solid #e74c3c;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 1rem; padding: 1rem; }
            .nav-links { justify-content: center; gap: 0.5rem; }
            .nav-links a { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
            .container { padding: 1rem; }
            .video-info { padding: 1.2rem; }
            .video-title { font-size: 1.3rem; }
            .bg-shape { display: none; }
            .comments-section { padding: 1.2rem; }
            .comment-form { flex-direction: column; }
            .comment-btn { width: 100%; justify-content: center; text-align: center; }
            .video-meta { font-size: 0.8rem; gap: 0.8rem; }
            .like-section { flex-wrap: wrap; }
            .video-container { border-radius: 16px; }
        }
        @media (max-width: 480px) {
            .logo { font-size: 1.1rem; }
            .video-title { font-size: 1.1rem; }
            .video-info { padding: 1rem; }
            .comments-section { padding: 1rem; }
            .comment { padding: 0.8rem; }
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
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="profile.php">Мои видео</a>
                <a href="upload.php"> Загрузить</a>
                <a href="account.php" class="user-name"> <?= htmlspecialchars($_SESSION['username']) ?></a>
                <a href="logout.php" class="btn btn-logout">Выйти</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline">Вход</a>
                <a href="register.php" class="btn btn-primary">Регистрация</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <a href="index.php" class="back-link">← Назад к видео</a>

        <div class="video-container">
            <div class="video-wrapper">
                <video controls>
                    <source src="<?= htmlspecialchars($video['video_path']) ?>" type="video/mp4">
                    Ваш браузер не поддерживает видео
                </video>
            </div>
            <div class="video-info">
                <h1 class="video-title"><?= htmlspecialchars($video['title']) ?></h1>
                <div class="video-meta">
                    <span class="author">Автор: <?= htmlspecialchars($video['username']) ?></span>
                    <span>Дата: <?= date('d.m.Y', strtotime($video['created_at'])) ?></span>
                    <span>Просмотров: <?= $video['views'] ?></span>
                </div>
                <div class="video-description">
                    <?= nl2br(htmlspecialchars($video['description'])) ?>
                </div>
                <div class="like-section">
                    <button id="likeBtn" class="like-btn <?= $user_liked ? 'liked' : '' ?>" onclick="toggleLike(<?= $video_id ?>)">
                        <?= $user_liked ? '❤️ Нравится' : '🤍 Нравится' ?>
                    </button>
                    <span class="like-count" id="likeCount">❤️ <?= $likes_count ?></span>
                </div>
            </div>
        </div>

        <div class="comments-section">
            <div class="comments-title">
                Комментарии
                <span class="count"><?= count($comments) ?></span>
            </div>

            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="comment-form">
                    <input type="text" id="commentInput" class="comment-input" placeholder="Напишите комментарий...">
                    <button class="comment-btn" onclick="addComment(<?= $video_id ?>)">Отправить</button>
                </div>
            <?php else: ?>
                <p style="color: rgba(255,255,255,0.5); margin-bottom: 1rem;">
                    <a href="login.php" style="color: white; font-weight: 600;">Войдите</a>, чтобы оставить комментарий
                </p>
            <?php endif; ?>

            <div class="comments-list" id="commentsList">
                <?php if(count($comments) > 0): ?>
                    <?php foreach($comments as $comment): ?>
                        <div class="comment" id="comment-<?= $comment['id'] ?>">
                            <div class="comment-header">
                                <span class="comment-author"><?= htmlspecialchars($comment['username']) ?></span>
                                <span class="comment-date"><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                                <?php if(isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $comment['user_id'] || $_SESSION['role'] == 'admin')): ?>
                                    <a href="watch.php?id=<?= $video_id ?>&delete_comment=<?= $comment['id'] ?>" class="delete-comment">Удалить</a>
                                <?php endif; ?>
                            </div>
                            <div class="comment-text"><?= nl2br(htmlspecialchars($comment['content'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-comments">Пока нет комментариев. Будьте первым!</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="toast"></div>

    <script>
        function showToast(message, type) {
            type = type || 'success';
            const toast = document.getElementById('toast');
            toast.innerHTML = '<div class="toast ' + type + '">' + message + '</div>';
            setTimeout(function() {
                toast.innerHTML = '';
            }, 2000);
        }

        function toggleLike(videoId) {
            <?php if(!isset($_SESSION['user_id'])): ?>
                showToast('Войдите в систему, чтобы поставить лайк', 'error');
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 1500);
                return;
            <?php endif; ?>
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'api/toggle_like.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            var likeBtn = document.getElementById('likeBtn');
                            var likeCount = document.getElementById('likeCount');
                            if (data.liked) {
                                likeBtn.classList.add('liked');
                                likeBtn.innerHTML = '❤️ Нравится';
                            } else {
                                likeBtn.classList.remove('liked');
                                likeBtn.innerHTML = '🤍 Нравится';
                            }
                            likeCount.innerHTML = '❤️ ' + data.likes_count;
                        }
                    } catch(e) {
                        console.log('Ошибка парсинга');
                    }
                }
            };
            xhr.send(JSON.stringify({ video_id: videoId }));
        }

        function addComment(videoId) {
            var content = document.getElementById('commentInput').value.trim();
            
            if (!content) {
                showToast('Введите текст комментария', 'error');
                return;
            }
            
            <?php if(!isset($_SESSION['user_id'])): ?>
                showToast('Войдите в систему, чтобы оставить комментарий', 'error');
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 1500);
                return;
            <?php endif; ?>
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'api/add_comment.php', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            var commentsList = document.getElementById('commentsList');
                            var now = new Date();
                            var dateStr = now.toLocaleDateString('ru-RU') + ' ' + now.toLocaleTimeString('ru-RU', {hour: '2-digit', minute:'2-digit'});
                            
                            var newComment = document.createElement('div');
                            newComment.className = 'comment';
                            newComment.innerHTML = '<div class="comment-header"><span class="comment-author">' + escapeHtml(data.username) + '</span><span class="comment-date">' + dateStr + '</span></div><div class="comment-text">' + escapeHtml(content) + '</div>';
                            
                            commentsList.insertBefore(newComment, commentsList.firstChild);
                            document.getElementById('commentInput').value = '';
                            
                            var noComments = commentsList.querySelector('.no-comments');
                            if (noComments) {
                                noComments.remove();
                            }
                            
                            var commentCount = document.querySelector('.comments-title .count');
                            var currentCount = parseInt(commentCount.textContent) || 0;
                            commentCount.textContent = currentCount + 1;
                        }
                    } catch(e) {
                        console.log('Ошибка парсинга');
                    }
                }
            };
            xhr.send(JSON.stringify({ video_id: videoId, content: content }));
        }

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    </script>
</body>
</html>