<?php
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    if ($login === 'admin' && $password === 'admin123') {
        $checkAdmin = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
        $checkAdmin->execute();
        $adminUser = $checkAdmin->fetch();

        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);

        if ($adminUser) {
            $updateAdmin = $pdo->prepare("UPDATE users SET password = ?, role = 'admin' WHERE username = 'admin'");
            $updateAdmin->execute([$hashedPassword]);
        } else {
            $insertAdmin = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES ('admin', 'admin@it.moscow', ?, 'admin')");
            $insertAdmin->execute([$hashedPassword]);
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'admin'");
        $stmt->execute();
        $user = $stmt->fetch();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();
    }

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header('Location: admin.php');
        } else {
            header('Location: index.php');
        }
        exit();
    } else {
        $error = 'Неверное имя пользователя/email или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - ВидеоПортфолио</title>
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
        .logo { text-align: center; margin-bottom: 25px; }
        .logo h2 { color: #fff; font-size: 1.8rem; font-weight: 700; margin-bottom: 8px; }
        .logo p { color: rgba(255,255,255,0.6); font-size: 0.95rem; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: rgba(255,255,255,0.85); font-size: 0.9rem; font-weight: 500; }
        .input-wrapper { position: relative; }
        .input-wrapper input {
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
        .input-wrapper input:focus {
            border-color: rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.14);
        }
        .input-wrapper input::placeholder { color: rgba(255,255,255,0.4); }
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
        .register-link { text-align: center; margin-top: 25px; color: rgba(255,255,255,0.6); font-size: 0.9rem; }
        .register-link a { color: #fff; text-decoration: none; font-weight: 600; margin-left: 5px; }
        .register-link a:hover { text-decoration: underline; }
        .alert-error { background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); color: #fca5a5; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 0.9rem; text-align: center; }
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
            <h2>Вход в систему</h2>
            <p>Войдите в свой аккаунт</p>
        </div>
        <?php if($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Имя пользователя или Email</label>
                <div class="input-wrapper">
                    <input type="text" name="login" placeholder="Введите логин или email" required autofocus>
                </div>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <div class="input-wrapper">
                    <input type="password" name="password" placeholder="Введите пароль" required>
                </div>
            </div>
            <button type="submit" class="btn-submit">Войти</button>
        </form>
        <div class="register-link">
            Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
        </div>
    </div>
</body>
</html>