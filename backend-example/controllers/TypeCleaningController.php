<?php
/**
 * Контроллер для типов уборки
 */
class TypeCleaningController
{
    /**
     * Получение всех типов уборки
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getAll($data)
    {
        $db = Database::getInstance();
        $typeCleanings = $db->fetchAll("SELECT * FROM type_cleaning ORDER BY id_type_cleaning ASC");
        
        sendSuccessResponse([
            'type_cleaning' => $typeCleanings
        ], 'Список типов уборки получен');
    }
    
    /**
     * Создание нового типа уборки (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function create($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверка данных
            $validation = validateData($data, [
                'title' => 'required|min:3',
                'description' => 'required|min:10',
                'price' => 'required|numeric'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Создание нового типа уборки
            $typeCleaningData = [
                'title' => $data['title'],
                'description' => $data['description'],
                'price' => $data['price']
            ];
            
            $db = Database::getInstance();
            $typeCleaningId = $db->insert('type_cleaning', $typeCleaningData);
            
            if (!$typeCleaningId) {
                sendErrorResponse('Ошибка при создании типа уборки', 500);
            }
            
            // Получение данных созданного типа уборки
            $typeCleaning = $db->fetch("SELECT * FROM type_cleaning WHERE id_type_cleaning = ?", [$typeCleaningId]);
            
            sendSuccessResponse([
                'type_cleaning' => $typeCleaning
            ], 'Тип уборки успешно создан');
        });
    }
    
    /**
     * Обновление типа уборки (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function update($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор типа уборки
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор типа уборки не указан', 400);
            }
            
            $typeCleaningId = $data['route_params']['id'];
            
            // Проверка данных
            $validation = validateData($data, [
                'title' => 'min:3',
                'description' => 'min:10',
                'price' => 'numeric'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Подготовка данных для обновления
            $updateData = [];
            
            if (isset($data['title'])) {
                $updateData['title'] = $data['title'];
            }
            
            if (isset($data['description'])) {
                $updateData['description'] = $data['description'];
            }
            
            if (isset($data['price'])) {
                $updateData['price'] = $data['price'];
            }
            
            // Если нет данных для обновления
            if (empty($updateData)) {
                sendErrorResponse('Нет данных для обновления', 400);
            }
            
            $db = Database::getInstance();
            
            // Проверка существования типа уборки
            $typeCleaning = $db->fetch("SELECT id_type_cleaning FROM type_cleaning WHERE id_type_cleaning = ?", [$typeCleaningId]);
            
            if (!$typeCleaning) {
                sendErrorResponse('Тип уборки не найден', 404);
            }
            
            // Обновление типа уборки
            $result = $db->update('type_cleaning', $updateData, 'id_type_cleaning = ?', [$typeCleaningId]);
            
            if (!$result) {
                sendErrorResponse('Ошибка при обновлении типа уборки', 500);
            }
            
            // Получение обновленных данных типа уборки
            $updatedTypeCleaning = $db->fetch("SELECT * FROM type_cleaning WHERE id_type_cleaning = ?", [$typeCleaningId]);
            
            sendSuccessResponse([
                'type_cleaning' => $updatedTypeCleaning
            ], 'Тип уборки успешно обновлен');
        });
    }
    
    /**
     * Удаление типа уборки (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function delete($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор типа уборки
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор типа уборки не указан', 400);
            }
            
            $typeCleaningId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования типа уборки
            $typeCleaning = $db->fetch("SELECT id_type_cleaning FROM type_cleaning WHERE id_type_cleaning = ?", [$typeCleaningId]);
            
            if (!$typeCleaning) {
                sendErrorResponse('Тип уборки не найден', 404);
            }
            
            // Проверка, не используется ли тип уборки в заказах
            $ordersCount = $db->fetch("SELECT COUNT(*) as count FROM orders WHERE id_type_cleaning = ?", [$typeCleaningId]);
            
            if ($ordersCount['count'] > 0) {
                sendErrorResponse('Невозможно удалить тип уборки, так как он используется в заказах', 400);
            }
            
            // Удаление типа уборки
            $result = $db->delete('type_cleaning', 'id_type_cleaning = ?', [$typeCleaningId]);
            
            if (!$result) {
                sendErrorResponse('Ошибка при удалении типа уборки', 500);
            }
            
            sendSuccessResponse([], 'Тип уборки успешно удален');
        });
    }
} 