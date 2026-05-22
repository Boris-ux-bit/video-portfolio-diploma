<?php
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Изменение роли
if (isset($_POST['change_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];
    $pdo->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$new_role, $user_id]);
    header('Location: users.php');
    exit();
}

// Удаление пользователя
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
    header('Location: users.php');
    exit();
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель - Управление пользователями</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f5f5;
            padding: 2rem;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 2rem;
        }
        h1 { margin-bottom: 1.5rem; }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f0f0f0;
        }
        select, button {
            padding: 0.25rem 0.5rem;
        }
        .btn-delete {
            color: red;
            text-decoration: none;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../index.php" class="back-link">← На главную</a>
        <h1>👥 Управление пользователями</h1>
        <table>
            <tr>
                <th>ID</th>
                <th>Имя</th>
                <th>Email</th>
                <th>Роль</th>
                <th>Дата регистрации</th>
                <th>Действия</th>
            </tr>
            <?php foreach($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['username']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <select name="role">
                            <option value="student" <?= $user['role'] == 'student' ? 'selected' : '' ?>>Студент</option>
                            <option value="teacher" <?= $user['role'] == 'teacher' ? 'selected' : '' ?>>Преподаватель</option>
                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Админ</option>
                        </select>
                        <button type="submit" name="change_role">Изменить</button>
                    </form>
                </td>
                <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                <td>
                    <?php if($user['id'] != $_SESSION['user_id']): ?>
                        <a href="?delete=<?= $user['id'] ?>" class="btn-delete" onclick="return confirm('Удалить пользователя?')">Удалить</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>