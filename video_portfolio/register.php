<?php
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'] ?? 'student';

    if (!in_array($role, ['student', 'teacher'])) {
        $role = 'student';
    }

    if ($password !== $confirm_password) {
        $error = 'Пароли не совпадают';
    } elseif (strlen($password) < 6) {
        $error = 'Пароль должен быть не менее 6 символов';
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);

        if ($check->fetch()) {
            $error = 'Пользователь с таким именем или email уже существует';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed, $role])) {
                $success = 'Регистрация успешна! Перенаправление на страницу входа...';
                header("Refresh: 2; url=login.php");
            } else {
                $error = 'Ошибка регистрации';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - ВидеоПортфолио</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        .bg-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1;
        }
        .bg-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
        }
        .bg-shape:nth-child(1) { width: 300px; height: 300px; background: rgba(255,255,255,0.15); top: -10%; left: -10%; }
        .bg-shape:nth-child(2) { width: 400px; height: 400px; background: rgba(102,126,234,0.3); bottom: -10%; right: -10%; }
        .bg-shape:nth-child(3) { width: 250px; height: 250px; background: rgba(118,75,162,0.3); top: 60%; left: -5%; }
        .bg-shape:nth-child(4) { width: 300px; height: 300px; background: rgba(255,255,255,0.1); top: 10%; right: 10%; }
        .login-card {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 40px;
            border-radius: 24px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
            z-index: 2;
        }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo h2 { color: #fff; font-size: 1.8rem; font-weight: 700; margin-bottom: 8px; }
        .logo p { color: rgba(255,255,255,0.6); font-size: 0.95rem; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: rgba(255,255,255,0.85); font-size: 0.9rem; font-weight: 500; }
        .input-wrapper { position: relative; }
        .input-wrapper input, .input-wrapper select {
            width: 100%;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 14px 16px;
            color: #fff;
            font-family: inherit;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
        }
        .input-wrapper input:focus, .input-wrapper select:focus {
            border-color: rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.14);
            box-shadow: 0 0 15px rgba(255,255,255,0.05);
        }
        .input-wrapper input::placeholder { color: rgba(255,255,255,0.4); }
        .role-selector { display: flex; gap: 12px; margin-top: 4px; }
        .role-selector label { flex: 1; cursor: pointer; margin: 0; }
        .role-selector input[type="radio"] { display: none; }
        .role-box {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 14px;
            text-align: center;
            color: #fff;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        .role-selector input[type="radio"]:checked + .role-box {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }
        .role-box:hover { background: rgba(255, 255, 255, 0.1); }
        .btn-submit {
            width: 100%;
            background: #fff;
            color: #1e1b4b;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
        }
        .btn-submit:hover { background: rgba(255,255,255,0.9); transform: translateY(-1px); }
        .login-link { text-align: center; margin-top: 25px; color: rgba(255,255,255,0.6); font-size: 0.9rem; }
        .login-link a { color: #fff; text-decoration: none; font-weight: 600; margin-left: 5px; }
        .login-link a:hover { text-decoration: underline; }
        .alert-error { background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 0.9rem; text-align: center; }
        .alert-success { background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); color: #a7f3d0; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 0.9rem; text-align: center; }
    </style>
</head>
<body>
    <div class="bg-shapes">
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
    </div>
    <div class="login-card">
        <div class="logo">
            <h2>Регистрация</h2>
            <p>Создайте новый аккаунт</p>
        </div>
        <?php if($error): ?>
            <div class=\"alert-error\"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class=\"alert-success\"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Имя пользователя</label>
                <div class="input-wrapper">
                    <input type="text" name="username" placeholder="Придумайте логин" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label>Email</label>
                <div class="input-wrapper">
                    <input type="email" name="email" placeholder="Введите email" required>
                </div>
            </div>
            <div class="form-group">
                <label>Выберите роль</label>
                <div class="role-selector">
                    <label>
                        <input type="radio" name="role" value="student" checked>
                        <div class="role-box">Студент</div>
                    </label>
                    <label>
                        <input type="radio" name="role" value="teacher">
                        <div class="role-box">Преподаватель</div>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label>Пароль (мин. 6 символов)</label>
                <div class="input-wrapper">
                    <input type="password" name="password" placeholder="Придумайте пароль" required>
                </div>
            </div>
            <div class="form-group">
                <label>Подтверждение пароля</label>
                <div class="input-wrapper">
                    <input type="password" name="confirm_password" placeholder="Повторите пароль" required>
                </div>
            </div>
            <button type="submit" class="btn-submit">Зарегистрироваться</button>
        </form>
        <div class="login-link">
            Уже есть аккаунт? <a href="login.php">Войти</a>
        </div>
    </div>
</body>
</html>