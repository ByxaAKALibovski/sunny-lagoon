<?php
/**
 * Файл конфигурации API
 */

// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'mr_cleaner');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Настройки JWT
define('JWT_SECRET', 'your_jwt_secret_key'); // Измените на случайную строку в продакшене
define('JWT_EXPIRATION', 24 * 60 * 60); // Срок действия токена в секундах (24 часа)

// Настройки загрузки файлов
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10 МБ
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'application/pdf']);

// Режим отладки
define('DEBUG', true);

// Отображение ошибок
if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Установка временной зоны
date_default_timezone_set('Europe/Moscow'); 