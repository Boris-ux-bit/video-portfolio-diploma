<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit();
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_user'])) {
        $user_id = $_POST['user_id'];
        if ($user_id != $_SESSION['user_id']) {
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
        }
        header('Location: admin.php');
        exit();
    }
    if (isset($_POST['change_role'])) {
        $user_id = $_POST['user_id'];
        $new_role = $_POST['role'];
        $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$new_role, $user_id]);
        header('Location: admin.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }
        .navbar {
            background: #2d2b4b;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .logo {
            color: white;
            font-size: 1.3rem;
            font-weight: 700;
        }
        .nav-links a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            margin-left: 1.5rem;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: 0.3s;
        }
        .nav-links a:hover {
            background: rgba(255,255,255,0.15);
            color: white;
        }
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .header h1 {
            color: #2d2b4b;
        }
        .header .admin-badge {
            background: #667eea;
            color: white;
            padding: 0.4rem 1.2rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            overflow-x: auto;
        }
        .card h2 {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        th {
            text-align: left;
            padding: 0.8rem 0.5rem;
            color: #666;
            font-weight: 600;
            border-bottom: 2px solid #eee;
        }
        td {
            padding: 0.8rem 0.5rem;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        tr:hover td {
            background: #f8f9ff;
        }
        select {
            padding: 0.3rem 0.6rem;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 0.85rem;
            background: white;
            cursor: pointer;
        }
        .btn {
            padding: 0.3rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: 0.3s;
        }
        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-success:hover {
            background: #219a52;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
        .role-badge {
            padding: 0.2rem 0.7rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .role-badge.admin { background: #e74c3c; color: white; }
        .role-badge.teacher { background: #3498db; color: white; }
        .role-badge.student { background: #2ecc71; color: white; }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat {
            background: white;
            border-radius: 12px;
            padding: 1.2rem;
            text-align: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
        }
        .stat .num {
            font-size: 2rem;
            font-weight: 700;
            color: #2d2b4b;
        }
        .stat .label {
            color: #888;
            font-size: 0.85rem;
            margin-top: 0.2rem;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #667eea;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 0.8rem; }
            .nav-links a { margin-left: 0; }
            .stats { grid-template-columns: 1fr; }
            .header { flex-direction: column; gap: 0.5rem; text-align: center; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo">🎓 Админ-панель</div>
        <div class="nav-links">
            <a href="index.php">Главная</a>
            <a href="profile.php">Мои видео</a>
            <a href="upload.php">📤 Загрузить</a>
            <a href="logout.php">Выйти</a>
        </div>
    </nav>

    <div class="container">

        <?php
        $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $total_videos = $pdo->query("SELECT COUNT(*) FROM videos")->fetchColumn();
        $total_comments = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn();
        $total_likes = $pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn();
        ?>

        <div class="header">
            <h1> Управление сайтом</h1>
            <span class="admin-badge"> Администратор</span>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="num"><?= $total_users ?></div>
                <div class="label"> Пользователей</div>
            </div>
            <div class="stat">
                <div class="num"><?= $total_videos ?></div>
                <div class="label"> Видео</div>
            </div>
            <div class="stat">
                <div class="num"><?= $total_comments ?></div>
                <div class="label"> Комментариев</div>
            </div>
            <div class="stat">
                <div class="num"><?= $total_likes ?></div>
                <div class="label"> Лайков</div>
            </div>
        </div>

        <div class="card">
            <h2> Пользователи</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
                <?php foreach($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="role-badge <?= $user['role'] ?>"><?= $user['role'] ?></span>
                    </td>
                    <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <form method="POST" style="display:inline-flex; gap: 0.3rem; flex-wrap:wrap;">
                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                            <select name="role">
                                <option value="student" <?= $user['role'] == 'student' ? 'selected' : '' ?>>Студент</option>
                                <option value="teacher" <?= $user['role'] == 'teacher' ? 'selected' : '' ?>>Преподаватель</option>
                                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Админ</option>
                            </select>
                            <button type="submit" name="change_role" class="btn btn-success">Изменить</button>
                            <?php if($user['id'] != $_SESSION['user_id']): ?>
                                <button type="submit" name="delete_user" class="btn btn-danger" onclick="return confirm('Удалить пользователя?')">Удалить</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

    </div>

</body>
</html>