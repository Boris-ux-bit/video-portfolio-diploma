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
        
        $allowed = ['mp4', 'webm', 'mov', 'avi'];
        $filename = $_FILES['video']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        
        if (!in_array($ext, $allowed)) {
            $error = 'Разрешены только форматы: MP4, WebM, MOV, AVI';
        } 
        
        elseif ($_FILES['video']['size'] > 1073741824) {
            $error = 'Файл не должен превышать 1 ГБ (примерно 1 час видео)';
        } 
        else {
            
            $upload_dir = 'uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            
            $new_filename = time() . '_' . uniqid() . '.' . $ext;
            $destination = $upload_dir . $new_filename;

            
            if (move_uploaded_file($_FILES['video']['tmp_name'], $destination)) {
                $stmt = $pdo->prepare("INSERT INTO videos (user_id, title, description, video_path) VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$_SESSION['user_id'], $title, $description, $destination])) {
                    $success = ' Видео успешно загружено!';
                    
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
            background: rgba(255,255,255,0.1);
            border-radius: 30px;
            font-size: 0.9rem;
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
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 5;
        }

        
        .upload-card {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 2.5rem;
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.3s;
        }

        .upload-card:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.12);
        }

        .upload-card .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .upload-card .header .icon {
            font-size: 3rem;
            display: block;
            margin-bottom: 0.5rem;
        }

        .upload-card .header h2 {
            font-size: 1.8rem;
            color: white;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }

        .upload-card .header p {
            color: rgba(255,255,255,0.6);
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: rgba(255,255,255,0.8);
            font-weight: 600;
            font-size: 0.95rem;
        }

        label .required {
            color: #ff6b6b;
            margin-left: 2px;
        }

        input, textarea {
            width: 100%;
            padding: 0.85rem 1.2rem;
            border: 2px solid rgba(255,255,255,0.12);
            border-radius: 16px;
            font-size: 1rem;
            transition: all 0.3s;
            background: rgba(255,255,255,0.06);
            color: white;
            font-family: 'Inter', sans-serif;
        }

        input::placeholder, textarea::placeholder {
            color: rgba(255,255,255,0.35);
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            box-shadow: 0 0 0 4px rgba(255,255,255,0.05);
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        
        .file-upload-wrapper {
            position: relative;
            border: 2px dashed rgba(255,255,255,0.15);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            background: rgba(255,255,255,0.03);
        }

        .file-upload-wrapper:hover {
            border-color: rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.06);
        }

        .file-upload-wrapper .upload-icon {
            font-size: 3rem;
            display: block;
            margin-bottom: 0.5rem;
        }

        .file-upload-wrapper .upload-text {
            color: rgba(255,255,255,0.6);
            font-size: 1rem;
        }

        .file-upload-wrapper .upload-text strong {
            color: white;
        }

        .file-upload-wrapper .file-name {
            color: rgba(255,255,255,0.8);
            font-weight: 500;
            margin-top: 0.5rem;
            display: none;
        }

        .file-upload-wrapper .file-name.show {
            display: block;
        }

        .file-upload-wrapper input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .file-info {
            margin-top: 1rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .file-info .tag {
            background: rgba(255,255,255,0.08);
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6);
            border: 1px solid rgba(255,255,255,0.05);
        }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 20px rgba(102,126,234,0.3);
            margin-top: 0.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 8px 30px rgba(102,126,234,0.4);
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        
        .alert {
            padding: 1rem 1.2rem;
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

        .back-link {
            display: inline-block;
            margin-top: 1.5rem;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }

        .back-link:hover {
            color: white;
            transform: translateX(-3px);
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
            .upload-card {
                padding: 1.5rem;
            }
            .upload-card .header h2 {
                font-size: 1.4rem;
            }
            .bg-shape {
                display: none;
            }
            .file-upload-wrapper {
                padding: 1.5rem;
            }
            .file-info {
                flex-direction: column;
                align-items: center;
            }
        }

        @media (max-width: 480px) {
            .logo {
                font-size: 1.1rem;
            }
            .upload-card .header .icon {
                font-size: 2.5rem;
            }
            input, textarea {
                padding: 0.7rem 1rem;
                font-size: 0.95rem;
            }
            .btn-submit {
                font-size: 1rem;
                padding: 0.85rem;
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
            <span class="user-name"> <?= htmlspecialchars($_SESSION['username']) ?></span>
            <a href="logout.php" class="btn btn-logout">Выйти</a>
        </div>
    </nav>

    <div class="container">
        <div class="upload-card">
            <div class="header">
                <span class="icon"></span>
                <h2>Загрузка нового видео</h2>
                <p>Загрузите своё видео, и оно появится в вашем профиле</p>
            </div>

            <?php if($error): ?>
                <div class="alert alert-error"> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert alert-success"> <?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="form-group">
                    <label>Название видео <span class="required">*</span></label>
                    <input type="text" name="title" value="<?= htmlspecialchars($title ?? '') ?>" required placeholder="Например: Лекция по программированию">
                </div>

                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" placeholder="Опишите, какие навыки вы продемонстрировали в этом видео..."><?= htmlspecialchars($description ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Видеофайл <span class="required">*</span></label>
                    <div class="file-upload-wrapper" id="fileDropZone">
                        <span class="upload-icon"></span>
                        <div class="upload-text">
                            <strong>Нажмите или перетащите</strong><br>
                            файл в эту область
                        </div>
                        <div class="file-name" id="fileName"> Файл выбран</div>
                        <input type="file" name="video" accept=".mp4,.webm,.mov,.avi" required id="fileInput">
                    </div>
                    <div class="file-info">
                        <span class="tag"> MP4, WebM, MOV, AVI</span>
                        <span class="tag"> до 1 ГБ (≈ 1 час видео)</span>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn"> Загрузить видео</button>
            </form>

            <a href="index.php" class="back-link">← Вернуться на главную</a>
        </div>
    </div>

    <script>
    
        const fileInput = document.getElementById('fileInput');
        const fileName = document.getElementById('fileName');

        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                fileName.textContent = ' ' + this.files[0].name;
                fileName.classList.add('show');
            } else {
                fileName.classList.remove('show');
            }
        });

        
        const dropZone = document.getElementById('fileDropZone');

        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = 'rgba(255,255,255,0.5)';
            this.style.background = 'rgba(255,255,255,0.1)';
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = 'rgba(255,255,255,0.15)';
            this.style.background = 'rgba(255,255,255,0.03)';
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = 'rgba(255,255,255,0.15)';
            this.style.background = 'rgba(255,255,255,0.03)';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileName.textContent = ' ' + files[0].name;
                fileName.classList.add('show');
            }
        });
    </script>

</body>
</html>