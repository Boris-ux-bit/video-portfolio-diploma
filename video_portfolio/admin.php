<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['change_role'])) {
        $user_id = intval($_POST['user_id']);
        $new_role = $_POST['role'];
        if (in_array($new_role, ['student', 'teacher', 'admin'])) {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$new_role, $user_id]);
            $message = 'Роль пользователя успешно изменена';
        }
    }

    if (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        if ($user_id != $_SESSION['user_id']) {
            $stmt = $pdo->prepare("SELECT video_path FROM videos WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $user_videos = $stmt->fetchAll();
            foreach ($user_videos as $video) {
                if (file_exists($video['video_path'])) {
                    unlink($video['video_path']);
                }
            }
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $message = 'Пользователь и все его видео успешно удалены';
        } else {
            $error = 'Вы не можете удалить самого себя';
        }
    }

    if (isset($_POST['delete_video'])) {
        $video_id = intval($_POST['video_id']);
        $stmt = $pdo->prepare("SELECT video_path FROM videos WHERE id = ?");
        $stmt->execute([$video_id]);
        $video = $stmt->fetch();
        if ($video) {
            if (file_exists($video['video_path'])) {
                unlink($video['video_path']);
            }
            $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
            $stmt->execute([$video_id]);
            $message = 'Видеофайл успешно удален из системы';
        }
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$videos = $pdo->query("SELECT v.*, u.username FROM videos v JOIN users u ON v.user_id = u.id ORDER BY v.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - ВидеоПортфолио</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #fff;
            padding: 40px 20px;
            position: relative;
            overflow-x: hidden;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }
        .header-panel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 20px 30px;
            border-radius: 16px;
            margin-bottom: 30px;
        }
        .header-panel h1 { font-size: 1.5rem; font-weight: 700; }
        .btn-home {
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .btn-home:hover { background: rgba(255, 255, 255, 0.25); }
        .alert {
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 0.95rem;
        }
        .alert-success { background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); color: #a7f3d0; }
        .alert-error { background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; }
        .section-card {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .section-card h2 { font-size: 1.3rem; margin-bottom: 20px; font-weight: 600; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 14px; color: rgba(255,255,255,0.6); font-weight: 500; font-size: 0.85rem; text-transform: uppercase; border-bottom: 1px solid rgba(255,255,255,0.1); }
        td { padding: 14px; border-bottom: 1px solid rgba(255,255,255,0.06); font-size: 0.95rem; vertical-align: middle; }
        .role-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .role-admin { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }
        .role-teacher { background: rgba(245, 158, 11, 0.2); color: #fde047; border: 1px solid rgba(245, 158, 11, 0.3); }
        .role-student { background: rgba(59, 130, 246, 0.2); color: #bfdbfe; border: 1px solid rgba(59, 130, 246, 0.3); }
        select {
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255,255,255,0.15);
            color: #fff;
            padding: 8px 12px;
            border-radius: 8px;
            outline: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 0.9rem;
        }
        select option { background: #2d1b4e; color: #fff; }
        .btn {
            padding: 8px 14px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: opacity 0.2s;
            font-family: inherit;
        }
        .btn:hover { opacity: 0.9; }
        .btn-save { background: #fff; color: #1e1b4b; margin-right: 5px; }
        .btn-delete { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4); }
        .btn-delete:hover { background: rgba(239, 68, 68, 0.4); }
        .video-link { color: #fff; text-decoration: none; font-weight: 500; }
        .video-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-panel">
            <h1>Панель управления администратора</h1>
            <a href="index.php" class="btn-home">На главную</a>
        </div>

        <?php if($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="section-card">
            <h2>Управление пользователями</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Логин</th>
                            <th>Email</th>
                            <th>Текущая роль</th>
                            <th>Дата регистрации</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <span class="role-badge role-<?= $user['role'] ?>"><?= $user['role'] ?></span>
                            </td>
                            <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <form method="POST" style="display: inline-flex; align-items: center; gap: 5px;">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="role">
                                        <option value="student" <?= $user['role'] == 'student' ? 'selected' : '' ?>>Студент</option>
                                        <option value="teacher" <?= $user['role'] == 'teacher' ? 'selected' : '' ?>>Преподаватель</option>
                                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Администратор</option>
                                    </select>
                                    <button type="submit" name="change_role" class="btn btn-save">Сохранить</button>
                                    <?php if($user['id'] != $_SESSION['user_id']): ?>
                                        <button type="submit" name="delete_user" class="btn btn-delete" onclick="return confirm('Удалить пользователя и все его видеофайлы?')">Удалить</button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section-card">
            <h2>Управление загруженными видео</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название видео</th>
                            <th>Автор</th>
                            <th>Просмотры</th>
                            <th>Дата загрузки</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($videos) > 0): ?>
                            <?php foreach($videos as $video): ?>
                            <tr>
                                <td><?= $video['id'] ?></td>
                                <td>
                                    <a href="watch.php?id=<?= $video['id'] ?>" class="video-link" target="_blank">
                                        <?= htmlspecialchars($video['title']) ?>
                                    </td>
                                <td><?= htmlspecialchars($video['username']) ?></td>
                                <td>👁 <?= $video['views'] ?></td>
                                <td><?= date('d.m.Y', strtotime($video['created_at'])) ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="video_id" value="<?= $video['id'] ?>">
                                        <button type="submit" name="delete_video" class="btn btn-delete" onclick="return confirm('Вы уверены, что хотите удалить это видео?')">Удалить видео</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: rgba(255,255,255,0.4);">На платформе пока нет загруженных видео</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>