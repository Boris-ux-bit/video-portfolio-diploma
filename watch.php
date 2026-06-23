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

$avg_grade_stmt = $pdo->prepare("SELECT AVG(score) as avg_score, COUNT(*) as total_grades FROM grades WHERE video_id = ?");
$avg_grade_stmt->execute([$video_id]);
$grade_info = $avg_grade_stmt->fetch();
$avg_grade = $grade_info['avg_score'] ? round($grade_info['avg_score'], 1) : null;
$total_grades = $grade_info['total_grades'];

$current_teacher_grade = null;
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'teacher') {
    $teacher_grade_stmt = $pdo->prepare("SELECT score FROM grades WHERE user_id = ? AND video_id = ?");
    $teacher_grade_stmt->execute([$_SESSION['user_id'], $video_id]);
    $current_teacher_grade = $teacher_grade_stmt->fetchColumn();
}

if (isset($_GET['delete_comment']) && isset($_SESSION['user_id'])) {
    $comment_id = $_GET['delete_comment'];
    if ($_SESSION['role'] == 'admin') {
        $del = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $del->execute([$comment_id]);
    } else {
        $del = $pdo->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
        $del->execute([$comment_id, $_SESSION['user_id']]);
    }
    header("Location: watch.php?id=" . $video_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($video['title']) ?> - Просмотр</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #fff;
            padding: 40px 20px;
        }
        .container { max-width: 1000px; margin: 0 auto; }
        .back-link { display: inline-block; color: rgba(255,255,255,0.8); text-decoration: none; margin-bottom: 20px; font-weight: 500; }
        .back-link:hover { color: #fff; }
        .video-card {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            margin-bottom: 30px;
        }
        video { width: 100%; border-radius: 16px; background: #000; margin-bottom: 25px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); }
        .video-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .video-title { font-size: 1.8rem; font-weight: 700; }
        .actions-panel { display: flex; gap: 15px; align-items: center; }
        .btn-like {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            padding: 10px 20px;
            border-radius: 12px;
            cursor: pointer;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-like:hover { background: rgba(255,255,255,0.2); }
        .btn-like.liked { background: #ef4444; border-color: #ef4444; }
        .grade-badge {
            background: rgba(255, 215, 0, 0.2);
            border: 1px solid rgba(255, 215, 0, 0.4);
            color: #ffd700;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
        }
        .meta-info { color: rgba(255,255,255,0.6); font-size: 0.95rem; margin-bottom: 20px; display: flex; gap: 20px; }
        .description { font-size: 1.05rem; line-height: 1.6; color: rgba(255,255,255,0.9); border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; }
        .teacher-section {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            padding: 20px;
            margin-top: 25px;
        }
        .teacher-section h3 { font-size: 1.2rem; margin-bottom: 15px; color: #ffd700; }
        .grade-form { display: flex; align-items: center; gap: 15px; }
        .grade-select {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 10px 15px;
            border-radius: 10px;
            font-family: inherit;
            font-size: 1rem;
            outline: none;
        }
        .grade-select option { background: #1e1b4b; color: #fff; }
        .btn-grade {
            background: #ffd700;
            color: #1e1b4b;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-grade:hover { opacity: 0.9; }
        .comments-section {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 24px;
            padding: 30px;
        }
        .comments-section h2 { font-size: 1.4rem; margin-bottom: 20px; }
        .comment-form { margin-bottom: 30px; }
        .comment-form textarea {
            width: 100%;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 15px;
            color: #fff;
            font-family: inherit;
            font-size: 0.95rem;
            resize: vertical;
            min-height: 80px;
            outline: none;
            margin-bottom: 10px;
        }
        .btn-comment {
            background: #fff;
            color: #1e1b4b;
            border: none;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            cursor: pointer;
        }
        .comment-item {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 15px;
        }
        .comment-header { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.85rem; color: rgba(255,255,255,0.5); }
        .comment-author { font-weight: 600; color: #fff; font-size: 0.95rem; }
        .comment-text { font-size: 0.95rem; line-height: 1.5; }
        .delete-comment { color: #fca5a5; text-decoration: none; margin-left: 10px; }
        .delete-comment:hover { text-decoration: underline; }
        .no-comments { text-align: center; color: rgba(255,255,255,0.5); padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← На главную</a>
        
        <div class="video-card">
            <video src="<?= htmlspecialchars($video['video_path']) ?>" controls></video>
            
            <div class="video-header">
                <h1 class="video-title"><?= htmlspecialchars($video['title']) ?></h1>
                <div class="actions-panel">
                    <?php if($avg_grade): ?>
                        <div class="grade-badge">Оценка: <?= $avg_grade ?>/5 (Всего: <?= $total_grades ?>)</div>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="api/toggle_like.php?id=<?= $video_id ?>" class="btn-like <?= $user_liked ? 'liked' : '' ?>">
                            ❤️ Лайк (<?= $likes_count ?>)
                        </a>
                    <?php else: ?>
                        <div class="btn-like">❤️ Лайков: <?= $likes_count ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="meta-info">
                <span>👤 Автор: <?= htmlspecialchars($video['username']) ?></span>
                <span>👁️ Просмотров: <?= $video['views'] ?></span>
                <span>📅 Дата: <?= date('d.m.Y', strtotime($video['created_at'])) ?></span>
            </div>
            
            <div class="description">
                <?= nl2br(htmlspecialchars($video['description'])) ?>
            </div>

            <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] === 'teacher'): ?>
                <div class="teacher-section">
                    <h3>Панель оценки преподавателя</h3>
                    <form action="api/add_grade.php" method="POST" class="grade-form">
                        <input type="hidden" name="video_id" value="<?= $video_id ?>">
                        <label for="score">Выставите оценку за работу:</label>
                        <select name="score" id="score" class="grade-select" required>
                            <option value="5" <?= $current_teacher_grade == 5 ? 'selected' : '' ?>>5 — Отлично</option>
                            <option value="4" <?= $current_teacher_grade == 4 ? 'selected' : '' ?>>4 — Хорошо</option>
                            <option value="3" <?= $current_teacher_grade == 3 ? 'selected' : '' ?>>3 — Удовлетворительно</option>
                            <option value="2" <?= $current_teacher_grade == 2 ? 'selected' : '' ?>>2 — Неудовлетворительно</option>
                            <option value="1" <?= $current_teacher_grade == 1 ? 'selected' : '' ?>>1 — Плохо</option>
                        </select>
                        <button type="submit" class="btn-grade">
                            <?= $current_teacher_grade ? 'Обновить оценку' : 'Поставить оценку' ?>
                        </button>
                    </form>
                    <?php if($current_teacher_grade): ?>
                        <p style="margin-top: 10px; font-size: 0.9rem; color: rgba(255,255,255,0.7);">Ваша текущая оценка этому видео: <strong><?= $current_teacher_grade ?></strong></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="comments-section">
            <h2>Комментарии</h2>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <form action="api/add_comment.php" method="POST" class="comment-form">
                    <input type="hidden" name="video_id" value="<?= $video_id ?>">
                    <textarea name="content" placeholder="Напишите комментарий..." required></textarea>
                    <button type="submit" class="btn-comment">Отправить</button>
                </form>
            <?php endif; ?>

            <div class="comments-list">
                <?php if(!empty($comments)): ?>
                    <?php foreach($comments as $comment): ?>
                        <div class="comment-item">
                            <div class="comment-header">
                                <span class="comment-author"><?= htmlspecialchars($comment['username']) ?></span>
                                <div>
                                    <span><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                                    <?php if(isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $comment['user_id'] || $_SESSION['role'] == 'admin')): ?>
                                        <a href="watch.php?id=<?= $video_id ?>&delete_comment=<?= $comment['id'] ?>" class="delete-comment">Удалить</a>
                                    <?php endif; ?>
                                </div>
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
</body>
</html>