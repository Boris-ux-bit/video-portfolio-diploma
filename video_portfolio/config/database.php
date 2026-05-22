<?php

ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');

ini_set('max_execution_time', '300');
ini_set('memory_limit', '256M');


$host = 'localhost';
$dbname = 'video_portfolio';
$username = 'root';
$password = 'root';      
$port = 8889;            

try {
    
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);
    
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    
    $pdo->exec("SET NAMES utf8");
    
} catch(PDOException $e) {
    
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>