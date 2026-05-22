<?php
require_once 'config/database.php';

$video_id = $_GET['id'] ?? 0;

// Получаем видео
$stmt = $pdo->prepare("SELECT v.*, u.username, u.role FROM videos v JOIN users u ON v.user_id = u.id WHERE v.id = ?");
$stmt->execute([$video_id]);
$video = $stmt->fetch();

if (!$video) {
    die('Видео не найдено');
}

// Увеличиваем просмотры
$pdo->prepare("UPDATE videos SET views = views + 1 WHERE id = ?")->execute([$video_id]);

// Получаем комментарии
$comments = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.video_id = ? ORDER BY c.created_at DESC");
$comments->execute([$video_id]);
$comments = $comments->fetchAll();

// Проверяем лайк от текущего пользователя
$user_liked = false;
if (isset($_SESSION['user_id'])) {
    $like_check = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND video_id = ?");
    $like_check->execute([$_SESSION['user_id'], $video_id]);
    $user_liked = $like_check->fetch();
}

// Количество лайков
$likes_count = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE video_id = ?");
$likes_count->execute([$video_id]);
$likes_count = $likes_count->fetchColumn();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($video['title']) ?> - ВидеоПортфолио</title>
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
        .video-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        video {
            width: 100%;
            background: #000;
        }
        .video-info {
            padding: 1.5rem;
        }
        h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .meta {
            color: #666;
            margin-bottom: 1rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .like-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .like-btn {
            background: #f0f0f0;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .like-btn.liked {
            background: #667eea;
            color: white;
        }
        .comments-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .comments-title {
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        .comment-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .comment-input {
            flex: 1;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 1rem;
        }
        .comment-btn {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
        }
        .comment {
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
        }
        .comment strong {
            color: #667eea;
        }
        .comment small {
            color: #999;
            font-size: 0.8rem;
            margin-left: 0.5rem;
        }
        .comment p {
            margin-top: 0.5rem;
            color: #555;
        }
        .delete-comment {
            color: #e74c3c;
            font-size: 0.75rem;
            text-decoration: none;
            margin-left: 1rem;
        }
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }
        .toast.success { background: #27ae60; }
        .toast.error { background: #e74c3c; }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">🎓 ВидеоПортфолио</div>
        <div class="nav-links">
            <a href="index.php">Главная</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="profile.php">Мои видео</a>
                <a href="upload.php">📤 Загрузить</a>
                <span>Привет, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="logout.php">Выйти</a>
            <?php else: ?>
                <a href="login.php">Вход</a>
                <a href="register.php">Регистрация</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <a href="index.php" class="back-link">← Назад к видео</a>
        
        <div class="video-container">
            <video controls>
                <source src="<?= htmlspecialchars($video['video_path']) ?>" type="video/mp4">
                Ваш браузер не поддерживает видео
            </video>
            <div class="video-info">
                <h1><?= htmlspecialchars($video['title']) ?></h1>
                <div class="meta">
                    <span>👤 Автор: <?= htmlspecialchars($video['username']) ?></span>
                    <span>📅 Дата: <?= date('d.m.Y', strtotime($video['created_at'])) ?></span>
                    <span>👁️ Просмотров: <?= $video['views'] ?></span>
                </div>
                <div class="description">
                    <?= nl2br(htmlspecialchars($video['description'])) ?>
                </div>
                <div class="like-section">
                    <button id="likeBtn" class="like-btn <?= $user_liked ? 'liked' : '' ?>" onclick="toggleLike(<?= $video_id ?>)">
                        <?= $user_liked ? '❤️ Нравится' : '🤍 Нравится' ?>
                    </button>
                    <span id="likeCount">❤️ <?= $likes_count ?></span>
                </div>
            </div>
        </div>

        <div class="comments-section">
            <h3 class="comments-title">💬 Комментарии (<?= count($comments) ?>)</h3>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="comment-form">
                    <input type="text" id="commentInput" class="comment-input" placeholder="Напишите комментарий...">
                    <button class="comment-btn" onclick="addComment(<?= $video_id ?>)">Отправить</button>
                </div>
            <?php else: ?>
                <p><a href="login.php">Войдите</a>, чтобы оставить комментарий</p>
            <?php endif; ?>
            
            <div id="commentsList">
                <?php foreach($comments as $comment): ?>
                    <div class="comment" id="comment-<?= $comment['id'] ?>">
                        <strong><?= htmlspecialchars($comment['username']) ?></strong>
                        <small><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></small>
                        <?php if(isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $comment['user_id'] || $_SESSION['role'] == 'admin')): ?>
                            <a href="watch.php?id=<?= $video_id ?>&delete_comment=<?= $comment['id'] ?>" class="delete-comment">Удалить</a>
                        <?php endif; ?>
                        <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="toast"></div>

    <script>
        // Функция показа уведомлений
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.innerHTML = `<div class="toast ${type}">${message}</div>`;
            setTimeout(() => {
                toast.innerHTML = '';
            }, 3000);
        }

        // Лайк без перезагрузки страницы
        async function toggleLike(videoId) {
            // Проверка, авторизован ли пользователь
            <?php if(!isset($_SESSION['user_id'])): ?>
                showToast('Войдите в систему, чтобы поставить лайк', 'error');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 1500);
                return;
            <?php endif; ?>
            
            try {
                const response = await fetch('api/toggle_like.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ video_id: videoId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const likeBtn = document.getElementById('likeBtn');
                    const likeCount = document.getElementById('likeCount');
                    
                    if (data.liked) {
                        likeBtn.classList.add('liked');
                        likeBtn.innerHTML = '❤️ Нравится';
                    } else {
                        likeBtn.classList.remove('liked');
                        likeBtn.innerHTML = '🤍 Нравится';
                    }
                    
                    likeCount.innerHTML = `❤️ ${data.likes_count}`;
                    showToast(data.liked ? 'Вы поставили лайк' : 'Вы убрали лайк');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                showToast('Ошибка при отправке лайка', 'error');
            }
        }

        // Добавление комментария без перезагрузки
        async function addComment(videoId) {
            const content = document.getElementById('commentInput').value.trim();
            
            if (!content) {
                showToast('Введите текст комментария', 'error');
                return;
            }
            
            <?php if(!isset($_SESSION['user_id'])): ?>
                showToast('Войдите в систему, чтобы оставить комментарий', 'error');
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 1500);
                return;
            <?php endif; ?>
            
            try {
                const response = await fetch('api/add_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        video_id: videoId, 
                        content: content 
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Добавляем новый комментарий в список
                    const commentsList = document.getElementById('commentsList');
                    const now = new Date();
                    const dateStr = now.toLocaleDateString('ru-RU') + ' ' + now.toLocaleTimeString('ru-RU', {hour: '2-digit', minute:'2-digit'});
                    
                    const newComment = `
                        <div class="comment">
                            <strong>${escapeHtml(data.username)}</strong>
                            <small>${dateStr}</small>
                            <p>${escapeHtml(content)}</p>
                        </div>
                    `;
                    
                    commentsList.insertAdjacentHTML('afterbegin', newComment);
                    document.getElementById('commentInput').value = '';
                    showToast('Комментарий добавлен');
                    
                    // Обновляем счетчик комментариев
                    const commentCount = document.querySelector('.comments-title');
                    const currentCount = parseInt(commentCount.innerHTML.match(/\d+/) || 0);
                    commentCount.innerHTML = `💬 Комментарии (${currentCount + 1})`;
                } else {
                    showToast(data.error || 'Ошибка добавления комментария', 'error');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                showToast('Ошибка при отправке комментария', 'error');
            }
        }

        // Защита от XSS-атак
        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }
    </script>
</body>
</html>