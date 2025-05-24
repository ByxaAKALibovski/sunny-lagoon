# Шаблон для создания нового API на PHP

Этот шаблон предназначен для быстрого создания нового API на чистом PHP без использования фреймворков и Composer. API включает JWT авторизацию, обработку multipart/form-data запросов и систему миграций.

## Структура файлов и папок

```
backend/
├── config/
│   └── config.php         # Конфигурация API (база данных, JWT, загрузка файлов)
├── controllers/           # Контроллеры
│   └── EntityController.php # Пример контроллера для новой сущности
├── database/              
│   ├── Database.php       # Класс для работы с базой данных
│   └── migrations.php     # Система миграций
├── auth/                  
│   └── jwt_handler.php    # Обработчик JWT токенов
├── helpers/               
│   └── functions.php      # Вспомогательные функции
├── router/                
│   └── Router.php         # Маршрутизатор
├── routes/                
│   └── routes.php         # Определение маршрутов
├── uploads/               # Папка для загруженных файлов
├── docs/                  
│   └── index.html         # API документация
└── index.php              # Входная точка API
```

## Пример создания нового контроллера

```php
<?php
/**
 * Контроллер для сущности Entity
 */
class EntityController
{
    /**
     * Получение всех записей
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getAll($data)
    {
        $db = Database::getInstance();
        $entities = $db->fetchAll("SELECT * FROM entity ORDER BY id_entity ASC");
        
        sendSuccessResponse([
            'entities' => $entities
        ], 'Список записей получен');
    }
    
    /**
     * Получение одной записи по ID
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getOne($data)
    {
        // Проверяем, передан ли идентификатор записи
        if (!isset($data['route_params']['id'])) {
            sendErrorResponse('Идентификатор записи не указан', 400);
        }
        
        $entityId = $data['route_params']['id'];
        $db = Database::getInstance();
        
        // Получение записи
        $entity = $db->fetch("SELECT * FROM entity WHERE id_entity = ?", [$entityId]);
        
        if (!$entity) {
            sendErrorResponse('Запись не найдена', 404);
        }
        
        sendSuccessResponse([
            'entity' => $entity
        ], 'Запись получена');
    }
    
    /**
     * Создание новой записи (с проверкой авторизации)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function create($data)
    {
        // Для запросов, требующих авторизации
        authMiddleware(function($user) use ($data) {
            // Проверка данных
            $validation = validateData($data, [
                'field1' => 'required|min:3',
                'field2' => 'required|numeric'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Подготовка данных для создания
            $entityData = [
                'field1' => $data['field1'],
                'field2' => $data['field2'],
                'user_id' => $user['id_users'] // Привязка к текущему пользователю
            ];
            
            // Обработка загруженного файла, если он есть
            if (isset($data['files']) && isset($data['files']['file'])) {
                $uploadDir = 'uploads/entity/';
                $filePath = handleUploadedFile($data['files'], 'file', $uploadDir);
                
                if ($filePath) {
                    $entityData['file_path'] = str_replace(UPLOAD_DIR, '', $filePath);
                }
            }
            
            // Создание записи
            $db = Database::getInstance();
            $entityId = $db->insert('entity', $entityData);
            
            if (!$entityId) {
                sendErrorResponse('Ошибка при создании записи', 500);
            }
            
            // Получение данных созданной записи
            $entity = $db->fetch("SELECT * FROM entity WHERE id_entity = ?", [$entityId]);
            
            sendSuccessResponse([
                'entity' => $entity
            ], 'Запись успешно создана');
        });
    }
    
    /**
     * Обновление записи (с проверкой прав администратора)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function update($data)
    {
        // Для запросов, требующих прав администратора
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор записи
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор записи не указан', 400);
            }
            
            $entityId = $data['route_params']['id'];
            
            // Проверка данных
            $validation = validateData($data, [
                'field1' => 'min:3',
                'field2' => 'numeric'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            $db = Database::getInstance();
            
            // Проверка существования записи
            $entity = $db->fetch("SELECT * FROM entity WHERE id_entity = ?", [$entityId]);
            
            if (!$entity) {
                sendErrorResponse('Запись не найдена', 404);
            }
            
            // Подготовка данных для обновления
            $updateData = [];
            
            if (isset($data['field1'])) {
                $updateData['field1'] = $data['field1'];
            }
            
            if (isset($data['field2'])) {
                $updateData['field2'] = $data['field2'];
            }
            
            // Обработка загруженного файла, если он есть
            if (isset($data['files']) && isset($data['files']['file'])) {
                $uploadDir = 'uploads/entity/';
                $filePath = handleUploadedFile($data['files'], 'file', $uploadDir);
                
                if ($filePath) {
                    $updateData['file_path'] = str_replace(UPLOAD_DIR, '', $filePath);
                    
                    // Удаление старого файла, если он существует
                    if (!empty($entity['file_path'])) {
                        $oldFilePath = UPLOAD_DIR . $entity['file_path'];
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                }
            }
            
            // Если нет данных для обновления
            if (empty($updateData)) {
                sendErrorResponse('Нет данных для обновления', 400);
            }
            
            // Обновление записи
            $result = $db->update('entity', $updateData, 'id_entity = ?', [$entityId]);
            
            if (!$result) {
                sendErrorResponse('Ошибка при обновлении записи', 500);
            }
            
            // Получение обновленных данных записи
            $updatedEntity = $db->fetch("SELECT * FROM entity WHERE id_entity = ?", [$entityId]);
            
            sendSuccessResponse([
                'entity' => $updatedEntity
            ], 'Запись успешно обновлена');
        });
    }
    
    /**
     * Удаление записи (с проверкой прав администратора)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function delete($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор записи
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор записи не указан', 400);
            }
            
            $entityId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования записи
            $entity = $db->fetch("SELECT * FROM entity WHERE id_entity = ?", [$entityId]);
            
            if (!$entity) {
                sendErrorResponse('Запись не найдена', 404);
            }
            
            // Удаление файла, если он существует
            if (!empty($entity['file_path'])) {
                $filePath = UPLOAD_DIR . $entity['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Удаление записи
            $result = $db->delete('entity', 'id_entity = ?', [$entityId]);
            
            if (!$result) {
                sendErrorResponse('Ошибка при удалении записи', 500);
            }
            
            sendSuccessResponse([], 'Запись успешно удалена');
        });
    }
}
```

## Пример добавления маршрутов в routes/routes.php

```php
// Подключение контроллера
require_once 'controllers/EntityController.php';

// Маршруты для сущности Entity
$router->get('entity', 'EntityController::getAll');
$router->get('entity/{id}', 'EntityController::getOne');
$router->post('entity', 'EntityController::create');
$router->put('entity/{id}', 'EntityController::update');
$router->delete('entity/{id}', 'EntityController::delete');
```

## Добавление миграции для новой сущности

В файле `database/migrations.php` добавьте определение новой таблицы:

```php
// Таблица для новой сущности
$this->tables['entity'] = "
    CREATE TABLE IF NOT EXISTS entity (
        id_entity INT AUTO_INCREMENT PRIMARY KEY,
        field1 VARCHAR(255) NOT NULL,
        field2 FLOAT NOT NULL,
        file_path VARCHAR(255) NULL,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id_users) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
```

Затем в методе `migrate()` добавьте новую таблицу в массив `$tableOrder`.

## Запуск API

1. Создайте базу данных MySQL
2. Настройте параметры подключения в `config/config.php`
3. Запустите миграции: `/backend/database/migrations.php?action=migrate`
4. Обращайтесь к API через базовый URL: `/backend/`

## Особенности API

- Аутентификация через JWT токены
- Поддержка загрузки файлов через form-data в POST и PUT запросах
- Система миграций для БД
- Отключенные CORS ограничения для тестирования 