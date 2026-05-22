<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (empty($title)) {
        $error = 'Введите название видео';
    } elseif (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        // Разрешенные форматы видео
        $allowed = ['mp4', 'webm', 'mov', 'avi'];
        $filename = $_FILES['video']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        // Проверка расширения файла
        if (!in_array($ext, $allowed)) {
            $error = 'Разрешены только форматы: MP4, WebM, MOV, AVI';
        } 
        // Проверка размера файла (100 МБ)
        elseif ($_FILES['video']['size'] > 100 * 1024 * 1024) {
            $error = 'Файл не должен превышать 100 МБ';
        } 
        else {
            // Создаем папку uploads, если её нет
            $upload_dir = 'uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Генерируем уникальное имя файла
            $new_filename = time() . '_' . uniqid() . '.' . $ext;
            $destination = $upload_dir . $new_filename;

            // Перемещаем загруженный файл
            if (move_uploaded_file($_FILES['video']['tmp_name'], $destination)) {
                $stmt = $pdo->prepare("INSERT INTO videos (user_id, title, description, video_path) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$_SESSION['user_id'], $title, $description, $destination])) {
                    $success = '✅ Видео успешно загружено!';
                    // Очищаем форму
                    $title = $description = '';
                } else {
                    $error = 'Ошибка сохранения в базу данных';
                }
            } else {
                $error = 'Ошибка загрузки файла';
            }
        }
    } else {
        $error = 'Выберите видеофайл';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Загрузка видео - ВидеоПортфолио</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
        }

        /* Геометрический фон */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                repeating-linear-gradient(45deg, rgba(255,255,255,0.05) 0px, rgba(255,255,255,0.05) 2px, transparent 2px, transparent 8px),
                repeating-linear-gradient(135deg, rgba(255,255,255,0.03) 0px, rgba(255,255,255,0.03) 1px, transparent 1px, transparent 12px);
            pointer-events: none;
            z-index: 0;
        }

        .bg-circle {
            position: fixed;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            pointer-events: none;
            z-index: 0;
            animation: float 20s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-50px) rotate(180deg); }
        }

        .navbar {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            position: relative;
            z-index: 10;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 25px;
        }

        .nav-links a:hover {
            color: #667eea;
            background: rgba(102,126,234,0.1);
            transform: translateY(-2px);
        }

        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-outline {
            border: 2px solid #667eea;
            color: #667eea;
            background: transparent;
        }

        .btn-outline:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .container {
            max-width: 600px;
            margin: 3rem auto;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            position: relative;
            z-index: 5;
        }

        h2 {
            margin-bottom: 1.5rem;
            color: #333;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }

        input, textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.2);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button[type="submit"] {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102,126,234,0.4);
        }

        .error {
            background: #fee;
            color: #c33;
            padding: 0.75rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border-left: 4px solid #c33;
        }

        .success {
            background: #efe;
            color: #3c3;
            padding: 0.75rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border-left: 4px solid #3c3;
        }

        .info {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.5rem;
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
    </style>
</head>
<body>
    <!-- Анимированные круги на фоне -->
    <div class="bg-circle" style="width: 300px; height: 300px; top: 10%; left: -100px; animation-duration: 25s;"></div>
    <div class="bg-circle" style="width: 200px; height: 200px; bottom: 20%; right: -50px; animation-duration: 30s;"></div>
    <div class="bg-circle" style="width: 400px; height: 400px; top: 50%; left: 70%; animation-duration: 20s;"></div>

    <nav class="navbar">
        <div class="logo">🎓 ВидеоПортфолио</div>
        <div class="nav-links">
            <a href="index.php">Главная</a>
            <a href="profile.php">Мои видео</a>
            <a href="upload.php">📤 Загрузить</a>
            <span>Привет, <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="logout.php" class="btn btn-outline">Выйти</a>
        </div>
    </nav>

    <div class="container">
        <a href="index.php" class="back-link">← На главную</a>
        
        <h2>📤 Загрузка нового видео</h2>
        
        <?php if($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Название видео *</label>
                <input type="text" name="title" value="<?= htmlspecialchars($title ?? '') ?>" required placeholder="Например: Лабораторная работа №3">
            </div>
            
            <div class="form-group">
                <label>Описание</label>
                <textarea name="description" placeholder="Опишите, какие навыки вы продемонстрировали в этом видео..."><?= htmlspecialchars($description ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label>📹 Видеофайл *</label>
                <input type="file" name="video" accept=".mp4,.webm,.mov,.avi" required>
                <div class="info">
                    📌 Поддерживаемые форматы: MP4, WebM, MOV, AVI<br>
                    📌 Максимальный размер: 100 МБ
                </div>
            </div>
            
            <button type="submit">🚀 Загрузить видео</button>
        </form>
    </div>
</body>
</html>