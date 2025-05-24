<?php
/**
 * Обработчик JWT токенов
 */

/**
 * Создание JWT токена
 * 
 * @param array $payload Данные для добавления в токен
 * @return string Готовый JWT токен
 */
function createJWT($payload) {
    // Заголовок
    $header = json_encode([
        'typ' => 'JWT',
        'alg' => 'HS256'
    ]);
    
    // Добавляем время истечения токена
    $payload['exp'] = time() + JWT_EXPIRATION;
    
    // Кодируем заголовок и полезную нагрузку
    $base64UrlHeader = base64UrlEncode($header);
    $base64UrlPayload = base64UrlEncode(json_encode($payload));
    
    // Создаем подпись
    $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, JWT_SECRET, true);
    $base64UrlSignature = base64UrlEncode($signature);
    
    // Собираем JWT токен
    $jwt = $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
    
    return $jwt;
}

/**
 * Проверка JWT токена
 * 
 * @param string $jwt JWT токен для проверки
 * @return array|false Данные из токена или false при ошибке
 */
function verifyJWT($jwt) {
    // Разбиваем токен на части
    $parts = explode('.', $jwt);
    
    if (count($parts) !== 3) {
        return false;
    }
    
    list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;
    
    // Проверяем подпись
    $signature = base64UrlDecode($base64UrlSignature);
    $expectedSignature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, JWT_SECRET, true);
    
    if (!hash_equals($signature, $expectedSignature)) {
        return false;
    }
    
    // Декодируем полезную нагрузку
    $payload = json_decode(base64UrlDecode($base64UrlPayload), true);
    
    // Проверяем время истечения токена
    if (isset($payload['exp']) && $payload['exp'] < time()) {
        return false;
    }
    
    return $payload;
}

/**
 * Получение данных пользователя из токена
 * 
 * @return array|false Данные пользователя или false, если токен недействителен
 */
function getUserFromToken() {
    // Проверяем наличие заголовка Authorization
    $headers = getallheaders();
    
    if (!isset($headers['Authorization']) && !isset($headers['authorization'])) {
        return false;
    }
    
    // Получаем токен из заголовка
    $authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : $headers['authorization'];
    $token = str_replace('Bearer ', '', $authHeader);
    
    if (empty($token)) {
        return false;
    }
    
    // Проверяем токен
    return verifyJWT($token);
}

/**
 * Base64Url кодирование
 * 
 * @param string $data Данные для кодирования
 * @return string Закодированная строка
 */
function base64UrlEncode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/**
 * Base64Url декодирование
 * 
 * @param string $data Данные для декодирования
 * @return string Декодированная строка
 */
function base64UrlDecode($data) {
    $padding = strlen($data) % 4;
    if ($padding > 0) {
        $data .= str_repeat('=', 4 - $padding);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

/**
 * Middleware для проверки авторизации пользователя
 * 
 * @param function $callback Функция для выполнения, если пользователь авторизован
 * @return void
 */
function authMiddleware($callback) {
    $user = getUserFromToken();
    
    if (!$user) {
        sendErrorResponse('Вы не авторизованы', 401);
    }
    
    // Проверяем, существует ли пользователь в базе данных
    $db = Database::getInstance();
    $dbUser = $db->fetch("SELECT * FROM users WHERE id_users = ? AND email = ?", [$user['id'], $user['email']]);
    
    if (!$dbUser) {
        sendErrorResponse('Пользователь не найден', 401);
    }
    
    // Запускаем переданную функцию с данными пользователя
    $callback($dbUser);
}

/**
 * Middleware для проверки прав администратора
 * 
 * @param function $callback Функция для выполнения, если пользователь - администратор
 * @return void
 */
function adminMiddleware($callback) {
    authMiddleware(function($user) use ($callback) {
        // Проверяем, является ли пользователь администратором
        if ($user['is_admin'] != 1) {
            sendErrorResponse('Доступ запрещен', 403);
        }
        
        // Запускаем переданную функцию с данными пользователя
        $callback($user);
    });
} 