<?php
/**
 * Контроллер для аутентификации
 */
class AuthController
{
    /**
     * Авторизация пользователя
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function login($data)
    {
        // Проверка данных
        $validation = validateData($data, [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if ($validation !== true) {
            sendErrorResponse($validation, 400);
        }
        
        // Поиск пользователя
        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$data['email']]);
        
        if (!$user || !password_verify($data['password'], $user['password'])) {
            sendErrorResponse('Неверный email или пароль', 401);
        }
        
        // Создание JWT токена
        $token = createJWT([
            'id' => $user['id_users'],
            'email' => $user['email'],
            'is_admin' => (bool) $user['is_admin'],
            'exp' => time() + JWT_EXPIRATION
        ]);
        
        // Удаление пароля из данных пользователя
        unset($user['password']);
        
        sendSuccessResponse([
            'user' => $user,
            'token' => $token
        ], 'Авторизация успешна');
    }

    /**
     * Регистрация нового пользователя
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function register($data)
    {
        // Проверка данных
        $validation = validateData($data, [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);
        
        if ($validation !== true) {
            sendErrorResponse($validation, 400);
        }
        
        $db = Database::getInstance();
        
        // Проверка существования пользователя
        $existingUser = $db->fetch("SELECT id_users FROM users WHERE email = ?", [$data['email']]);
        if ($existingUser) {
            sendErrorResponse('Пользователь с таким email уже существует', 400);
        }
        
        // Создание пользователя
        $userData = [
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'is_admin' => 0
        ];
        
        $userId = $db->insert('users', $userData);
        
        if (!$userId) {
            sendErrorResponse('Ошибка при создании пользователя', 500);
        }
        
        // Получение данных созданного пользователя
        $user = $db->fetch("SELECT * FROM users WHERE id_users = ?", [$userId]);
        unset($user['password']);
        
        // Создание JWT токена
        $token = createJWT([
            'id' => $user['id_users'],
            'email' => $user['email'],
            'is_admin' => (bool) $user['is_admin'],
            'exp' => time() + JWT_EXPIRATION
        ]);
        
        sendSuccessResponse([
            'user' => $user,
            'token' => $token
        ], 'Регистрация успешна');
    }

    /**
     * Изменение пароля пользователя
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function changePassword($data)
    {
        authMiddleware(function($user) use ($data) {
            // Проверка данных
            $validation = validateData($data, [
                'current_password' => 'required',
                'new_password' => 'required|min:6'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            $db = Database::getInstance();
            
            // Проверка текущего пароля
            $currentUser = $db->fetch("SELECT * FROM users WHERE id_users = ?", [$user['id_users']]);
            if (!password_verify($data['current_password'], $currentUser['password'])) {
                sendErrorResponse('Неверный текущий пароль', 400);
            }
            
            // Обновление пароля
            $newPasswordHash = password_hash($data['new_password'], PASSWORD_DEFAULT);
            if (!$db->update('users', ['password' => $newPasswordHash], 'id_users = ?', [$user['id_users']])) {
                sendErrorResponse('Ошибка при обновлении пароля', 500);
            }
            
            sendSuccessResponse([], 'Пароль успешно изменен');
        });
    }
} 