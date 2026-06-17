<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Получаем данные пользователя
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: logout.php');
    exit();
}


$update_success = '';
$update_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    if (empty($username) || empty($email)) {
        $update_error = 'Все поля обязательны для заполнения';
    } else {
        // Проверка на уникальность
        $check = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $check->execute([$username, $email, $_SESSION['user_id']]);
        
        if ($check->fetch()) {
            $update_error = 'Пользователь с таким именем или email уже существует';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$username, $email, $_SESSION['user_id']])) {
                $_SESSION['username'] = $username;
                $update_success = 'Профиль успешно обновлён!';
                
                $user['username'] = $username;
                $user['email'] = $email;
            } else {
                $update_error = 'Ошибка обновления профиля';
            }
        }
    }
}


$video_count = $pdo->prepare("SELECT COUNT(*) FROM videos WHERE user_id = ?");
$video_count->execute([$_SESSION['user_id']]);
$video_count = $video_count->fetchColumn();

$likes_received = $pdo->prepare("
    SELECT COUNT(*) FROM likes l 
    JOIN videos v ON l.video_id = v.id 
    WHERE v.user_id = ?
");
$likes_received->execute([$_SESSION['user_id']]);
$likes_received = $likes_received->fetchColumn();

$total_views = $pdo->prepare("SELECT SUM(views) FROM videos WHERE user_id = ?");
$total_views->execute([$_SESSION['user_id']]);
$total_views = $total_views->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - ВидеоПортфолио</title>
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
            position: relative;
        }

        /* ГЕОМЕТРИЧЕСКИЙ ФОН С АНИМАЦИЕЙ */
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
            background: rgba(255,255,255,0.15);
            border-radius: 30px;
            font-size: 0.9rem;
            cursor: default;
        }

        .nav-links .user-name.active {
            background: rgba(255,255,255,0.25);
            box-shadow: 0 0 20px rgba(102,126,234,0.2);
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

      
        .profile-card {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2.5rem;
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.3s;
            margin-bottom: 2rem;
        }

        .profile-card:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.12);
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
            box-shadow: 0 8px 30px rgba(102,126,234,0.3);
            flex-shrink: 0;
            position: relative;
            border: 3px solid rgba(255,255,255,0.2);
            transition: all 0.3s;
        }

        .profile-avatar:hover {
            transform: scale(1.02);
            border-color: rgba(255,255,255,0.4);
        }

        .profile-avatar .status-dot {
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 18px;
            height: 18px;
            background: #27ae60;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.2);
        }

        .profile-info {
            flex: 1;
            min-width: 200px;
        }

        .profile-info .name {
            font-size: 1.8rem;
            color: white;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }

        .profile-info .role-badge {
            display: inline-block;
            padding: 0.2rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(255,255,255,0.15);
            color: rgba(255,255,255,0.8);
            margin-bottom: 0.8rem;
        }

        .profile-info .role-badge.admin {
            background: rgba(231, 76, 60, 0.3);
            color: #ff6b6b;
        }

        .profile-info .role-badge.teacher {
            background: rgba(52, 152, 219, 0.3);
            color: #5dade2;
        }

        .profile-info .role-badge.student {
            background: rgba(46, 204, 113, 0.3);
            color: #55efc4;
        }

        .profile-info .details {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
        }

        .profile-info .details .item {
            color: rgba(255,255,255,0.7);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .profile-info .details .item strong {
            color: rgba(255,255,255,0.9);
            font-weight: 600;
            min-width: 140px;
        }

        .profile-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            flex-wrap: wrap;
        }

        .btn-edit {
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

        .btn-edit:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 30px rgba(102,126,234,0.4);
        }

        .btn-cancel {
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

        .btn-cancel:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.5);
            transform: translateY(-2px);
        }

        .btn-upload-profile {
            border: 2px solid rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.8);
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

        .btn-upload-profile:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.4);
            transform: translateY(-2px);
        }

        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-item {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(15px);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.06);
            transition: all 0.3s;
        }

        .stat-item:hover {
            transform: translateY(-3px);
            background: rgba(255,255,255,0.16);
            border-color: rgba(255,255,255,0.1);
        }

        .stat-item .number {
            font-size: 2.2rem;
            font-weight: 800;
            color: white;
            line-height: 1;
        }

        .stat-item .label {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.6);
            margin-top: 0.3rem;
            font-weight: 500;
        }

        .stat-item .emoji {
            font-size: 1.5rem;
            display: block;
            margin-bottom: 0.3rem;
        }

        
        .edit-form {
            display: none;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.08);
        }

        .edit-form.active {
            display: block;
        }

        .edit-form .form-group {
            margin-bottom: 1rem;
        }

        .edit-form label {
            display: block;
            margin-bottom: 0.4rem;
            color: rgba(255,255,255,0.8);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .edit-form input {
            width: 100%;
            padding: 0.8rem 1.2rem;
            border: 2px solid rgba(255,255,255,0.12);
            border-radius: 16px;
            font-size: 1rem;
            transition: all 0.3s;
            background: rgba(255,255,255,0.06);
            color: white;
            font-family: 'Inter', sans-serif;
        }

        .edit-form input::placeholder {
            color: rgba(255,255,255,0.35);
        }

        .edit-form input:focus {
            outline: none;
            border-color: rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            box-shadow: 0 0 0 4px rgba(255,255,255,0.05);
        }

        .edit-form .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }

        .edit-form .btn-save {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 30px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            box-shadow: 0 4px 15px rgba(46,204,113,0.3);
        }

        .edit-form .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(46,204,113,0.4);
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

        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            border: 1px solid rgba(46, 204, 113, 0.2);
            color: #55efc4;
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid rgba(231, 76, 60, 0.2);
            color: #ff6b6b;
        }

       
        .footer {
            text-align: center;
            color: rgba(255,255,255,0.4);
            padding: 2rem 0 1rem;
            font-size: 0.85rem;
        }

        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }
            .nav-links {
                justify-content: center;
                gap: 0.5rem;
            }
            .nav-links a {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
            .container {
                padding: 1rem;
            }
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            .profile-info .details .item {
                justify-content: center;
            }
            .profile-actions {
                justify-content: center;
            }
            .bg-shape {
                display: none;
            }
            .profile-avatar {
                width: 80px;
                height: 80px;
                font-size: 2.5rem;
            }
            .profile-info .name {
                font-size: 1.4rem;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .edit-form .form-actions {
                flex-direction: column;
            }
            .edit-form .form-actions .btn-save,
            .edit-form .form-actions .btn-cancel {
                width: 100%;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .logo {
                font-size: 1.1rem;
            }
            .profile-card {
                padding: 1.5rem;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .stat-item .number {
                font-size: 1.8rem;
            }
            .profile-info .details .item strong {
                min-width: 100px;
                font-size: 0.85rem;
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
        <div class="bg-shape"></div>
    </div>

    
    <nav class="navbar">
        <div class="logo">
             <span>ВидеоПортфолио</span>
        </div>
        <div class="nav-links">
            <a href="index.php">Главная</a>
            <a href="profile.php">Мои видео</a>
            <a href="upload.php"> Загрузить</a>
            <a href="account.php" class="user-name active"> <?= htmlspecialchars($_SESSION['username']) ?></a>
            <a href="logout.php" class="btn btn-logout">Выйти</a>
        </div>
    </nav>

    <div class="container">

        
        <div class="profile-card">
            <?php if($update_success): ?>
                <div class="alert alert-success"> <?= $update_success ?></div>
            <?php endif; ?>
            <?php if($update_error): ?>
                <div class="alert alert-error"> <?= $update_error ?></div>
            <?php endif; ?>

            <div class="profile-header">
                <div class="profile-avatar">
                    <?= mb_substr($user['username'], 0, 1, 'UTF-8') ?>
                    <span class="status-dot"></span>
                </div>
                <div class="profile-info">
                    <div class="name"><?= htmlspecialchars($user['username']) ?></div>
                    <span class="role-badge <?= $user['role'] ?>">
                        <?= $user['role'] == 'admin' ? ' Администратор' : ($user['role'] == 'teacher' ? ' Преподаватель' : ' Студент') ?>
                    </span>
                    <div class="details">
                        <div class="item"><strong> Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
                        <div class="item"><strong> Дата регистрации:</strong> <?= date('d.m.Y', strtotime($user['created_at'])) ?></div>
                        <div class="item"><strong> ID:</strong> #<?= $user['id'] ?></div>
                    </div>
                </div>
            </div>

            <div class="profile-actions">
                <button class="btn-edit" id="showEditForm"> Редактировать профиль</button>
                <a href="profile.php" class="btn-upload-profile"> Мои видео</a>
                <a href="upload.php" class="btn-upload-profile"> Загрузить видео</a>
            </div>

           
            <div class="edit-form" id="editForm">
                <form method="POST">
                    <div class="form-group">
                        <label>Имя пользователя</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" name="update_profile" class="btn-save"> Сохранить изменения</button>
                        <button type="button" class="btn-cancel" id="hideEditForm">Отмена</button>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="stats-grid">
            <div class="stat-item">
                <span class="emoji"></span>
                <div class="number"><?= $video_count ?></div>
                <div class="label">Всего видео</div>
            </div>
            <div class="stat-item">
                <span class="emoji"></span>
                <div class="number"><?= $likes_received ?></div>
                <div class="label">Всего лайков</div>
            </div>
            <div class="stat-item">
                <span class="emoji"></span>
                <div class="number"><?= $total_views ?></div>
                <div class="label">Всего просмотров</div>
            </div>
            <div class="stat-item">
                <span class="emoji"></span>
                <div class="number"><?= date('d.m.Y', strtotime($user['created_at'])) ?></div>
                <div class="label">С нами с</div>
            </div>
        </div>

        
        <div class="footer">
            © 2026 ВидеоПортфолио. Все права защищены.
        </div>
    </div>

    <script>
        
        document.getElementById('showEditForm').addEventListener('click', function() {
            document.getElementById('editForm').classList.add('active');
            this.style.display = 'none';
        });

        
        document.getElementById('hideEditForm').addEventListener('click', function() {
            document.getElementById('editForm').classList.remove('active');
            document.getElementById('showEditForm').style.display = 'inline-block';
        });
    </script>

</body>
</html>