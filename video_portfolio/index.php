<?php
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ВидеоПортфолио - Учебные видео студентов</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
        .nav-links .user-link {
            color: white;
            font-weight: 600;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 30px;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
        }
        .nav-links .user-link:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-2px);
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
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102,126,234,0.4);
        }
        .btn-primary:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 30px rgba(102,126,234,0.5);
        }
        .btn-outline {
            border: 2px solid rgba(255,255,255,0.5);
            color: white;
            background: transparent;
        }
        .btn-outline:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
            border-color: white;
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
        .search-section {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 1.5rem 2rem;
            margin-bottom: 2.5rem;
            border: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s;
        }
        .search-section:hover {
            background: rgba(255,255,255,0.15);
            border-color: rgba(255,255,255,0.15);
        }
        .search-form {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .search-input-wrap {
            flex: 1;
            position: relative;
        }
        .search-input-wrap .icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.5);
            font-size: 1.1rem;
        }
        .search-input {
            width: 100%;
            padding: 0.85rem 1rem 0.85rem 3rem;
            border: 2px solid rgba(255,255,255,0.15);
            border-radius: 30px;
            font-size: 1rem;
            transition: all 0.3s;
            background: rgba(255,255,255,0.08);
            color: white;
            font-family: 'Inter', sans-serif;
        }
        .search-input::placeholder {
            color: rgba(255,255,255,0.5);
        }
        .search-input:focus {
            outline: none;
            border-color: rgba(255,255,255,0.4);
            background: rgba(255,255,255,0.15);
            box-shadow: 0 0 0 4px rgba(255,255,255,0.05);
        }
        .sort-select {
            padding: 0.85rem 1.5rem;
            border: 2px solid rgba(255,255,255,0.15);
            border-radius: 30px;
            background: rgba(255,255,255,0.08);
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='rgba(255,255,255,0.6)' stroke-width='2' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
        }
        .sort-select option {
            background: #2d2b4b;
            color: white;
        }
        .sort-select:focus {
            outline: none;
            border-color: rgba(255,255,255,0.4);
        }
        .btn-search {
            padding: 0.85rem 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
        }
        .btn-search:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 30px rgba(102,126,234,0.4);
        }
        .loader {
            text-align: center;
            padding: 3rem;
            display: none;
        }
        .loader.active {
            display: block;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255,255,255,0.1);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .videos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }
        .video-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .video-card:hover {
            transform: translateY(-10px) scale(1.01);
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        .video-thumbnail {
            width: 100%;
            height: 200px;
            position: relative;
            overflow: hidden;
            background: #1a1a2e;
        }
        .video-thumbnail video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .video-thumbnail .thumb-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, transparent 50%, rgba(0,0,0,0.4) 100%);
        }
        .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            width: 65px;
            height: 65px;
            background: rgba(255,255,255,0.95);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-size: 1.8rem;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .play-overlay svg {
            width: 28px;
            height: 28px;
            margin-left: 4px;
        }
        .video-card:hover .play-overlay {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
        .video-thumbnail .video-stats-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            display: flex;
            gap: 0.5rem;
        }
        .video-thumbnail .badge {
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 500;
        }
        .video-info {
            padding: 1.5rem;
        }
        .video-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #1a1a2e;
            line-height: 1.3;
        }
        .video-meta {
            color: #888;
            font-size: 0.8rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .video-meta .author {
            font-weight: 500;
            color: #667eea;
        }
        .video-description {
            color: #777;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .video-stats {
            display: flex;
            gap: 1.5rem;
            color: #999;
            font-size: 0.8rem;
            padding-top: 1rem;
            border-top: 1px solid #f0f0f0;
        }
        .video-stats span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        .video-stats .icon-heart {
            color: #e74c3c;
        }
        .watch-btn {
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.6rem 1.8rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }
        .watch-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(102,126,234,0.4);
        }
        .no-videos {
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.08);
            color: white;
        }
        .no-videos .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        .no-videos h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        .no-videos p {
            color: rgba(255,255,255,0.6);
        }
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: rgba(0,0,0,0.85);
            backdrop-filter: blur(15px);
            color: white;
            padding: 14px 28px;
            border-radius: 16px;
            z-index: 1000;
            animation: slideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            font-weight: 500;
        }
        .toast.success {
            border-left: 4px solid #27ae60;
        }
        .toast.error {
            border-left: 4px solid #e74c3c;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 1rem; padding: 1rem; }
            .nav-links { justify-content: center; gap: 0.5rem; }
            .nav-links a { padding: 0.4rem 0.8rem; font-size: 0.8rem; }
            .videos-grid { grid-template-columns: 1fr; }
            .search-section { padding: 1rem; }
            .search-form { flex-direction: column; }
            .search-input-wrap { width: 100%; }
            .sort-select { width: 100%; }
            .btn-search { width: 100%; text-align: center; }
            .container { padding: 1rem; }
            .bg-shape { display: none; }
            .video-thumbnail { height: 180px; }
        }
        @media (max-width: 480px) {
            .logo { font-size: 1.1rem; }
            .video-card { border-radius: 15px; }
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
        <div class="logo"> <span>ВидеоПортфолио</span></div>
        <div class="nav-links">
            <a href="index.php">Главная</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="profile.php">Мои видео</a>
                <a href="upload.php"> Загрузить</a>
                <a href="account.php" class="user-link"> <?= htmlspecialchars($_SESSION['username']) ?></a>
                <a href="logout.php" class="btn btn-logout">Выйти</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline">Вход</a>
                <a href="register.php" class="btn btn-primary">Регистрация</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <div class="search-section">
            <div class="search-form">
                <div class="search-input-wrap">
                    <span class="icon"></span>
                    <input type="text" id="searchInput" class="search-input" placeholder="Поиск видео по названию или описанию...">
                </div>
                <select id="sortSelect" class="sort-select">
                    <option value="newest"> Новые</option>
                    <option value="popular"> Популярные</option>
                </select>
                <button id="searchBtn" class="btn-search">Найти</button>
            </div>
        </div>

        <div class="loader" id="loader">
            <div class="spinner"></div>
        </div>

        <div id="videosContainer" class="videos-grid"></div>
    </div>

    <div id="toast"></div>

    <script>
        function showToast(message, type) {
            type = type || 'success';
            const toast = document.getElementById('toast');
            toast.innerHTML = '<div class="toast ' + type + '">' + message + '</div>';
            setTimeout(function() {
                toast.innerHTML = '';
            }, 3000);
        }

        async function loadVideos() {
            const search = document.getElementById('searchInput').value;
            const sort = document.getElementById('sortSelect').value;
            const loader = document.getElementById('loader');
            const container = document.getElementById('videosContainer');
            loader.classList.add('active');
            container.style.opacity = '0.4';
            container.style.transition = 'opacity 0.3s';
            try {
                const response = await fetch('api/get_videos.php?search=' + encodeURIComponent(search) + '&sort=' + sort);
                const data = await response.json();
                if (data.success) {
                    renderVideos(data.videos);
                } else {
                    container.innerHTML = '<div class="no-videos"><div class="icon"></div><h3>Видео не найдены</h3><p>Попробуйте изменить поисковый запрос</p></div>';
                }
            } catch (error) {
                console.error('Ошибка:', error);
                showToast('Ошибка загрузки видео', 'error');
                container.innerHTML = '<div class="no-videos"><div class="icon"></div><h3>Ошибка загрузки</h3><p>Попробуйте обновить страницу</p></div>';
            } finally {
                loader.classList.remove('active');
                container.style.opacity = '1';
            }
        }

        function renderVideos(videos) {
            const container = document.getElementById('videosContainer');
            if (videos.length === 0) {
                container.innerHTML = '<div class="no-videos"><div class="icon"></div><h3>Видео не найдены</h3><p>Попробуйте изменить поисковый запрос</p></div>';
                return;
            }
            container.innerHTML = videos.map(function(video) {
                return '<div class="video-card" onclick="window.location.href=\'watch.php?id=' + video.id + '\'">' +
                    '<div class="video-thumbnail">' +
                    '<video muted preload="metadata" style="width:100%; height:100%; object-fit:cover;">' +
                    '<source src="' + escapeHtml(video.video_path) + '" type="video/mp4">' +
                    '</video>' +
                    '<div class="thumb-overlay"></div>' +
                    '<div class="play-overlay">' +
                    '<svg viewBox="0 0 24 24" fill="currentColor"><polygon points="5,3 19,12 5,21"/></svg>' +
                    '</div>' +
                    '<div class="video-stats-badge">' +
                    '<span class="badge">❤️ ' + video.likes_count + '</span>' +
                    '<span class="badge">👁️ ' + video.views + '</span>' +
                    '</div>' +
                    '</div>' +
                    '<div class="video-info">' +
                    '<div class="video-title">' + escapeHtml(video.title) + '</div>' +
                    '<div class="video-meta">' +
                    '<span class="author"> ' + escapeHtml(video.username) + '</span>' +
                    '<span> ' + video.created_date + '</span>' +
                    '</div>' +
                    '<div class="video-description">' + escapeHtml(video.description.substring(0, 120)) + (video.description.length > 120 ? '...' : '') + '</div>' +
                    '<button class="watch-btn" onclick="event.stopPropagation(); window.location.href=\'watch.php?id=' + video.id + '\'">▶ Смотреть</button>' +
                    '</div>' +
                    '</div>';
            }).join('');
        }

        function escapeHtml(str) {
            if (!str) return '';
            var div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        }

        document.getElementById('searchBtn').addEventListener('click', loadVideos);
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') loadVideos();
        });
        document.getElementById('sortSelect').addEventListener('change', loadVideos);
        document.addEventListener('DOMContentLoaded', loadVideos);
    </script>
</body>
</html>