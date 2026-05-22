<?php
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ВидеоПортфолио - Учебные видео студентов</title>
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

        /* Геометрические фигуры на фоне */
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

        /* Анимированные круги */
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

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102,126,234,0.4);
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 5;
        }

        .search-section {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .search-form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.2);
        }

        .sort-select {
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }

        .sort-select:hover {
            border-color: #667eea;
        }

        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .video-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
            cursor: pointer;
        }

        .video-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        /* Стили для превью видео */
        .video-thumbnail {
            width: 100%;
            height: 200px;
            position: relative;
            overflow: hidden;
            background: #000;
        }

        .video-thumbnail video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
            background: rgba(0,0,0,0.7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            opacity: 0;
            transition: all 0.3s;
            backdrop-filter: blur(5px);
        }

        .video-card:hover .play-overlay {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1.1);
        }

        .video-info {
            padding: 1.5rem;
        }

        .video-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .video-meta {
            color: #666;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
        }

        .video-description {
            color: #777;
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .video-stats {
            display: flex;
            gap: 1rem;
            color: #999;
            font-size: 0.85rem;
        }

        .watch-btn {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.5rem 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-size: 0.85rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .watch-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }

        .no-videos {
            text-align: center;
            padding: 3rem;
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            color: #666;
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        }

        .toast.success { background: #27ae60; }
        .toast.error { background: #e74c3c; }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .loader {
            text-align: center;
            padding: 2rem;
            display: none;
        }

        .loader.active { display: block; }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255,255,255,0.3);
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 1rem; }
            .videos-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <!-- Геометрические фигуры на фоне -->
    <div class="bg-circle" style="width: 300px; height: 300px; top: 10%; left: -100px; animation-duration: 25s;"></div>
    <div class="bg-circle" style="width: 200px; height: 200px; bottom: 20%; right: -50px; animation-duration: 30s;"></div>
    <div class="bg-circle" style="width: 400px; height: 400px; top: 50%; left: 70%; animation-duration: 20s;"></div>
    <div class="bg-circle" style="width: 150px; height: 150px; bottom: 10%; left: 20%; animation-duration: 35s;"></div>

    <nav class="navbar">
        <div class="logo">🎓 ВидеоПортфолио</div>
        <div class="nav-links">
            <a href="index.php">Главная</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="profile.php">Мои видео</a>
                <a href="upload.php">📤 Загрузить</a>
                <span>Привет, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="logout.php" class="btn btn-outline">Выйти</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline">Вход</a>
                <a href="register.php" class="btn btn-primary">Регистрация</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="search-section">
            <div class="search-form">
                <input type="text" id="searchInput" class="search-input" 
                       placeholder="🔍 Поиск видео по названию или описанию...">
                <select id="sortSelect" class="sort-select">
                    <option value="newest">📅 Новые</option>
                    <option value="popular">🔥 Популярные</option>
                </select>
                <button id="searchBtn" class="btn btn-primary">Найти</button>
            </div>
        </div>

        <div class="loader" id="loader">
            <div class="spinner"></div>
        </div>

        <div id="videosContainer" class="videos-grid">
            <!-- Видео загружаются через AJAX -->
        </div>
    </div>

    <div id="toast"></div>

    <script>
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.innerHTML = `<div class="toast ${type}">${message}</div>`;
            setTimeout(() => {
                toast.innerHTML = '';
            }, 3000);
        }

        async function loadVideos() {
            const search = document.getElementById('searchInput').value;
            const sort = document.getElementById('sortSelect').value;
            
            const loader = document.getElementById('loader');
            const container = document.getElementById('videosContainer');
            
            loader.classList.add('active');
            container.style.opacity = '0.5';
            
            try {
                const response = await fetch(`api/get_videos.php?search=${encodeURIComponent(search)}&sort=${sort}`);
                const data = await response.json();
                
                if (data.success) {
                    renderVideos(data.videos);
                } else {
                    container.innerHTML = '<div class="no-videos">😕 Видео не найдены</div>';
                }
            } catch (error) {
                console.error('Ошибка:', error);
                showToast('Ошибка загрузки видео', 'error');
            } finally {
                loader.classList.remove('active');
                container.style.opacity = '1';
            }
        }

        // Функция отображения видео с ПРЕВЬЮ (первый кадр)
        function renderVideos(videos) {
            const container = document.getElementById('videosContainer');
            
            if (videos.length === 0) {
                container.innerHTML = '<div class="no-videos">😕 Видео не найдены<br>Попробуйте изменить поисковый запрос</div>';
                return;
            }
            
            container.innerHTML = videos.map(video => `
                <div class="video-card" onclick="window.location.href='watch.php?id=${video.id}'">
                    <div class="video-thumbnail">
                        <video muted preload="metadata" style="width:100%; height:100%; object-fit:cover;">
                            <source src="${escapeHtml(video.video_path)}" type="video/mp4">
                            Ваш браузер не поддерживает видео
                        </video>
                        <div class="play-overlay">▶</div>
                    </div>
                    <div class="video-info">
                        <div class="video-title">${escapeHtml(video.title)}</div>
                        <div class="video-meta">
                            <span>👤 ${escapeHtml(video.username)}</span>
                            <span>📅 ${video.created_date}</span>
                        </div>
                        <div class="video-description">${escapeHtml(video.description.substring(0, 100))}${video.description.length > 100 ? '...' : ''}</div>
                        <div class="video-stats">
                            <span>❤️ ${video.likes_count}</span>
                            <span>👁️ ${video.views}</span>
                        </div>
                        <div class="watch-btn" onclick="event.stopPropagation(); window.location.href='watch.php?id=${video.id}'">▶ Смотреть</div>
                    </div>
                </div>
            `).join('');
        }

        function escapeHtml(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        document.getElementById('searchBtn').addEventListener('click', loadVideos);
        document.getElementById('searchInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') loadVideos();
        });
        document.getElementById('sortSelect').addEventListener('change', loadVideos);
        
        document.addEventListener('DOMContentLoaded', loadVideos);
    </script>
</body>
</html>