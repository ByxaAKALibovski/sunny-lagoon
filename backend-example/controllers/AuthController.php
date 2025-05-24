<?php
/**
 * Контроллер авторизации и регистрации
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
            'password' => 'required|min:6'
        ]);
        
        if ($validation !== true) {
            sendErrorResponse($validation, 400);
        }
        
        // Получение пользователя из БД
        $db = Database::getInstance();
        $user = $db->fetch("SELECT * FROM users WHERE email = ?", [$data['email']]);
        
        // Проверка пользователя и пароля
        if (!$user || !password_verify($data['password'], $user['password'])) {
            sendErrorResponse('Неверный логин или пароль', 401);
        }
        
        // Создание JWT токена
        $token = createJWT([
            'id' => $user['id_users'],
            'email' => $user['email'],
            'is_admin' => (bool) $user['is_admin']
        ]);
        
        // Удаление пароля из данных пользователя
        unset($user['password']);
        
        sendSuccessResponse([
            'token' => $token,
            'user' => $user
        ], 'Вы успешно авторизованы');
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
            'FIO' => 'required|min:3',
            'email' => 'required|email',
            'phone' => 'required|phone',
            'password' => 'required|min:6'
        ]);
        
        if ($validation !== true) {
            sendErrorResponse($validation, 400);
        }
        
        // Проверка, что пользователь с таким email не существует
        $db = Database::getInstance();
        $existingUser = $db->fetch("SELECT id_users FROM users WHERE email = ?", [$data['email']]);
        
        if ($existingUser) {
            sendErrorResponse('Пользователь с таким email уже существует', 400);
        }
        
        // Хеширование пароля
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Создание пользователя
        $userId = $db->insert('users', [
            'FIO' => $data['FIO'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $hashedPassword,
            'is_admin' => 0
        ]);
        
        if (!$userId) {
            sendErrorResponse('Ошибка при регистрации пользователя', 500);
        }
        
        // Получение данных созданного пользователя
        $user = $db->fetch("SELECT * FROM users WHERE id_users = ?", [$userId]);
        
        // Создание JWT токена
        $token = createJWT([
            'id' => $user['id_users'],
            'email' => $user['email'],
            'is_admin' => (bool) $user['is_admin']
        ]);
        
        // Удаление пароля из данных пользователя
        unset($user['password']);
        
        sendSuccessResponse([
            'token' => $token,
            'user' => $user
        ], 'Вы успешно зарегистрированы');
    }
    
    /**
     * Получение данных профиля пользователя
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getProfile($data)
    {
        authMiddleware(function($user) {
            // Удаление пароля из данных пользователя
            unset($user['password']);
            
            sendSuccessResponse([
                'user' => $user
            ], 'Данные профиля получены');
        });
    }
    
    /**
     * Обновление данных профиля пользователя
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function updateProfile($data)
    {
        authMiddleware(function($user) use ($data) {
            // Проверка данных
            $validation = validateData($data, [
                'FIO' => 'min:3',
                'phone' => 'phone',
                'room_area' => 'numeric'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Подготовка данных для обновления
            $updateData = [];
            
            if (isset($data['FIO'])) {
                $updateData['FIO'] = $data['FIO'];
            }
            
            if (isset($data['phone'])) {
                $updateData['phone'] = $data['phone'];
            }
            
            if (isset($data['room_area'])) {
                $updateData['room_area'] = $data['room_area'];
            }
            
            // Если нет данных для обновления
            if (empty($updateData)) {
                sendSuccessResponse(['user' => $user], 'Нет данных для обновления');
                return;
            }
            
            // Обновление данных пользователя
            $db = Database::getInstance();
            $result = $db->update('users', $updateData, 'id_users = ?', [$user['id_users']]);
            
            if (!$result) {
                sendErrorResponse('Ошибка при обновлении данных профиля', 500);
            }
            
            // Получение обновленных данных пользователя
            $updatedUser = $db->fetch("SELECT * FROM users WHERE id_users = ?", [$user['id_users']]);
            
            // Удаление пароля из данных пользователя
            unset($updatedUser['password']);
            
            sendSuccessResponse([
                'user' => $updatedUser
            ], 'Данные профиля обновлены');
        });
    }
} 