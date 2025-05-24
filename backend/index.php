<?php
/**
 * Основной файл API
 */

// Включение CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Обработка предварительных запросов OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Подключение необходимых файлов
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/helpers/functions.php';
require_once __DIR__ . '/router/Router.php';

// Создание экземпляра маршрутизатора
$router = new Router();

// Определение маршрутов

// Авторизация$router->post('auth/login', 'AuthController::login');$router->post('auth/register', 'AuthController::register');$router->put('auth/change-password', 'AuthController::changePassword');

// Категории
$router->get('categories', 'CategoryController::getAll');
$router->post('categories', 'CategoryController::create');
$router->put('categories/{id}', 'CategoryController::update');
$router->delete('categories/{id}', 'CategoryController::delete');

// Дома
$router->get('homes', 'HomeController::getAll');
$router->get('homes/{id}', 'HomeController::getOne');
$router->post('homes', 'HomeController::create');
$router->put('homes/{id}', 'HomeController::update');
$router->delete('homes/{id}', 'HomeController::delete');

// Услуги
$router->get('services', 'ServicesController::getAll');
$router->post('services', 'ServicesController::create');
$router->put('services/{id}', 'ServicesController::update');
$router->delete('services/{id}', 'ServicesController::delete');

// Развлечения
$router->get('gaiety', 'GaietyController::getAll');
$router->post('gaiety', 'GaietyController::create');
$router->put('gaiety/{id}', 'GaietyController::update');
$router->delete('gaiety/{id}', 'GaietyController::delete');

// Акции
$router->get('promotions', 'PromotionController::getAll');
$router->post('promotions', 'PromotionController::create');
$router->put('promotions/{id}', 'PromotionController::update');
$router->delete('promotions/{id}', 'PromotionController::delete');

// Бронирования
$router->get('reservations', 'ReservationController::getAll');
$router->post('reservations', 'ReservationController::create');
$router->put('reservations/{id}', 'ReservationController::update');

// Отзывы
$router->get('reviews', 'ReviewController::getAll');
$router->post('reviews', 'ReviewController::create');

// Обработка запроса
$router->handleRequest(); 