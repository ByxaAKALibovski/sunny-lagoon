<?php
/**
 * Файл с маршрутами API
 */

// Подключаем контроллеры
require_once 'controllers/AuthController.php';
require_once 'controllers/ApplicationController.php';
require_once 'controllers/OrderController.php';
require_once 'controllers/TypeCleaningController.php';
require_once 'controllers/CleaningController.php';
require_once 'controllers/ServiceController.php';
require_once 'controllers/ArticleController.php';
require_once 'controllers/ReviewController.php';

// Auth Routes
$router->post('auth/login', 'AuthController::login');
$router->post('auth/register', 'AuthController::register');
$router->get('auth/profile', 'AuthController::getProfile');
$router->put('auth/profile', 'AuthController::updateProfile');

// Application Routes
$router->post('application', 'ApplicationController::create');
$router->get('application', 'ApplicationController::getAll');

// Orders Routes
$router->get('orders', 'OrderController::getAll');
$router->get('orders/{id}', 'OrderController::getOne');
$router->put('orders/{id}', 'OrderController::update');
$router->post('orders', 'OrderController::create');
$router->get('orders/my', 'OrderController::getMyOrders');

// Type Cleaning Routes
$router->get('type-cleaning', 'TypeCleaningController::getAll');
$router->post('type-cleaning', 'TypeCleaningController::create');
$router->put('type-cleaning/{id}', 'TypeCleaningController::update');
$router->delete('type-cleaning/{id}', 'TypeCleaningController::delete');

// Cleaning Routes
$router->get('cleaning', 'CleaningController::getAll');
$router->post('cleaning', 'CleaningController::create');
$router->put('cleaning/{id}', 'CleaningController::update');
$router->delete('cleaning/{id}', 'CleaningController::delete');

// Services Routes
$router->get('services', 'ServiceController::getAll');
$router->post('services', 'ServiceController::create');
$router->put('services/{id}', 'ServiceController::update');
$router->delete('services/{id}', 'ServiceController::delete');

// Article Routes
$router->get('article', 'ArticleController::getAll');
$router->get('article/{id}', 'ArticleController::getOne');
$router->post('article', 'ArticleController::create');
$router->put('article/{id}', 'ArticleController::update');
$router->delete('article/{id}', 'ArticleController::delete');

// Reviews Routes
$router->get('reviews', 'ReviewController::getAll');
$router->post('reviews', 'ReviewController::create');

// API Documentation Route
$router->get('docs', function($data) {
    include 'docs/index.html';
    exit;
});

// Маршрут для корневого пути API, возвращает общую информацию
$router->get('', function($data) {
    sendSuccessResponse([
        'name' => 'MR Cleaner API',
        'version' => '1.0.0',
        'documentation' => getApiUrl() . '/docs',
        'endpoints' => [
            'auth' => [
                'login' => '/auth/login',
                'register' => '/auth/register',
                'profile' => '/auth/profile',
            ],
            'application' => '/application',
            'orders' => '/orders',
            'type-cleaning' => '/type-cleaning',
            'cleaning' => '/cleaning',
            'services' => '/services',
            'article' => '/article',
            'reviews' => '/reviews',
        ]
    ], 'Добро пожаловать в MR Cleaner API');
}); 