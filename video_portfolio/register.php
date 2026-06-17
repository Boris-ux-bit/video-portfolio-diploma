<?php
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

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
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'student')");
            if ($stmt->execute([$username, $email, $hashed])) {
                $success = 'Регистрация успешна! Перенаправление на страницу входа...';
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
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

        @keyframes floatShape {
            0%, 100% { transform: translate(0, 0) rotate(0deg) scale(1); }
            25% { transform: translate(30px, -30px) rotate(90deg) scale(1.05); }
            50% { transform: translate(-20px, 40px) rotate(180deg) scale(0.95); }
            75% { transform: translate(20px, -20px) rotate(270deg) scale(1.02); }
        }

        .register-card {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2.5rem;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255,255,255,0.08);
            position: relative;
            z-index: 5;
            transition: all 0.3s;
        }

        .register-card:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.12);
        }

        .register-card .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-card .logo .emoji {
            font-size: 3rem;
            display: block;
            margin-bottom: 0.5rem;
        }

        .register-card .logo h2 {
            font-size: 1.8rem;
            color: white;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .register-card .logo p {
            color: rgba(255,255,255,0.6);
            font-size: 0.95rem;
            margin-top: 0.2rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        label {
            display: block;
            margin-bottom: 0.4rem;
            color: rgba(255,255,255,0.8);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper .icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.4);
            font-size: 1.1rem;
            z-index: 2;
            pointer-events: none;
        }

        input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 3rem;
            border: 2px solid rgba(255,255,255,0.15);
            border-radius: 16px;
            font-size: 1rem;
            transition: all 0.3s;
            background: rgba(255,255,255,0.08);
            color: #ffffff;
            font-family: 'Inter', sans-serif;
            position: relative;
            z-index: 1;
        }

        input::placeholder {
            color: rgba(255,255,255,0.4);
        }

        input:focus {
            outline: none;
            border-color: rgba(255,255,255,0.4);
            background: rgba(255,255,255,0.15);
            box-shadow: 0 0 0 4px rgba(255,255,255,0.05);
        }

        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 1000px rgba(50, 45, 80, 0.9) inset !important;
            -webkit-text-fill-color: #ffffff !important;
            border-color: rgba(255,255,255,0.3) !important;
            background-color: rgba(50, 45, 80, 0.9) !important;
            background-clip: padding-box !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        input:-moz-autofill {
            background-color: rgba(50, 45, 80, 0.9) !important;
            color: #ffffff !important;
        }

        .btn-submit {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 20px rgba(102,126,234,0.3);
            margin-top: 0.3rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 8px 30px rgba(102,126,234,0.4);
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .alert {
            padding: 0.8rem 1.2rem;
            border-radius: 16px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            backdrop-filter: blur(10px);
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid rgba(231, 76, 60, 0.2);
            color: #ff6b6b;
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid rgba(46, 204, 113, 0.2);
            color: #55efc4;
        }

        .alert .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.2);
            border-top: 2px solid #55efc4;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 0.5rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: rgba(255,255,255,0.6);
            font-size: 0.95rem;
        }

        .login-link a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .register-card {
                padding: 1.8rem;
                margin: 1rem;
            }
            .register-card .logo h2 {
                font-size: 1.4rem;
            }
            .bg-shape {
                display: none;
            }
            input {
                padding: 0.75rem 1rem 0.75rem 2.8rem;
                font-size: 0.95rem;
            }
            .btn-submit {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>

    <div class="bg-shapes">
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
    </div>

    <div class="register-card">
        <div class="logo">
            <span class="emoji"></span>
            <h2>Регистрация</h2>
            <p>Создайте новый аккаунт</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
                <span class="spinner"></span>
            </div>
            <meta http-equiv="refresh" content="2;url=login.php">
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Имя пользователя</label>
                <div class="input-wrapper">
                    <span class="icon"></span>
                    <input type="text" name="username" placeholder="Придумайте логин" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <div class="input-wrapper">
                    <span class="icon"></span>
                    <input type="email" name="email" placeholder="Введите email" required>
                </div>
            </div>

            <div class="form-group">
                <label>Пароль <span style="color:rgba(255,255,255,0.4);font-weight:400;font-size:0.8rem;">(мин. 6 символов)</span></label>
                <div class="input-wrapper">
                    <span class="icon"></span>
                    <input type="password" name="password" placeholder="Придумайте пароль" required>
                </div>
            </div>

            <div class="form-group">
                <label>Подтверждение пароля</label>
                <div class="input-wrapper">
                    <span class="icon"></span>
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