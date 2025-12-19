<?php
session_start();

define('DB_PATH', __DIR__ . '/../data/students.db');

function getPDO() {
    if (!file_exists(DB_PATH)) {
        die("Файл базы данных не найден: " . DB_PATH);
    }
    
    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        die("Ошибка подключения к базе данных: " . $e->getMessage());
    }
}

function redirect($url) {
    header("Location: $url");
    exit;
}
?>