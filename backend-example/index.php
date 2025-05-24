<?php
/**
 * Основной входной файл API
 * MR Cleaner API
 */

// Отключение CORS политики
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Подключение конфигурации
require_once 'config/config.php';
// Подключение вспомогательных функций
require_once 'helpers/functions.php';
// Подключение обработчика JWT
require_once 'auth/jwt_handler.php';
// Подключение класса для работы с БД
require_once 'database/Database.php';
// Подключение маршрутизатора
require_once 'router/Router.php';

// Инициализация маршрутизатора
$router = new Router();
// Загрузка всех маршрутов
require_once 'routes/routes.php';
// Запуск маршрутизатора
$router->run(); 