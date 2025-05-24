<?php
/**
 * Контроллер для услуг
 */
class ServiceController
{
    /**
     * Получение всех услуг
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getAll($data)
    {
        $db = Database::getInstance();
        $services = $db->fetchAll("SELECT * FROM services ORDER BY id_services ASC");
        
        sendSuccessResponse([
            'services' => $services
        ], 'Список услуг получен');
    }
    
    /**
     * Создание новой услуги (только для администраторов)
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
                'price' => 'required|numeric'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Подготовка данных для создания
            $serviceData = [
                'title' => $data['title'],
                'price' => $data['price'],
                'comment' => isset($data['comment']) ? $data['comment'] : null
            ];
            
            // Создание услуги
            $db = Database::getInstance();
            $serviceId = $db->insert('services', $serviceData);
            
            if (!$serviceId) {
                sendErrorResponse('Ошибка при создании услуги', 500);
            }
            
            // Получение данных созданной услуги
            $service = $db->fetch("SELECT * FROM services WHERE id_services = ?", [$serviceId]);
            
            sendSuccessResponse([
                'service' => $service
            ], 'Услуга успешно создана');
        });
    }
    
    /**
     * Обновление услуги (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function update($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор услуги
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор услуги не указан', 400);
            }
            
            $serviceId = $data['route_params']['id'];
            
            // Проверка данных
            $validation = validateData($data, [
                'title' => 'min:3',
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
            
            if (isset($data['price'])) {
                $updateData['price'] = $data['price'];
            }
            
            if (isset($data['comment'])) {
                $updateData['comment'] = $data['comment'];
            }
            
            // Если нет данных для обновления
            if (empty($updateData)) {
                sendErrorResponse('Нет данных для обновления', 400);
            }
            
            $db = Database::getInstance();
            
            // Проверка существования услуги
            $service = $db->fetch("SELECT id_services FROM services WHERE id_services = ?", [$serviceId]);
            
            if (!$service) {
                sendErrorResponse('Услуга не найдена', 404);
            }
            
            // Обновление услуги
            $result = $db->update('services', $updateData, 'id_services = ?', [$serviceId]);
            
            if (!$result) {
                sendErrorResponse('Ошибка при обновлении услуги', 500);
            }
            
            // Получение обновленных данных услуги
            $updatedService = $db->fetch("SELECT * FROM services WHERE id_services = ?", [$serviceId]);
            
            sendSuccessResponse([
                'service' => $updatedService
            ], 'Услуга успешно обновлена');
        });
    }
    
    /**
     * Удаление услуги (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function delete($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор услуги
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор услуги не указан', 400);
            }
            
            $serviceId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования услуги
            $service = $db->fetch("SELECT id_services FROM services WHERE id_services = ?", [$serviceId]);
            
            if (!$service) {
                sendErrorResponse('Услуга не найдена', 404);
            }
            
            // Проверка, не используется ли услуга в заказах
            $ordersCount = $db->fetch("
                SELECT COUNT(*) as count 
                FROM orders_services 
                WHERE id_services = ?
            ", [$serviceId]);
            
            if ($ordersCount['count'] > 0) {
                sendErrorResponse('Невозможно удалить услугу, так как она используется в заказах', 400);
            }
            
            // Удаление услуги
            $result = $db->delete('services', 'id_services = ?', [$serviceId]);
            
            if (!$result) {
                sendErrorResponse('Ошибка при удалении услуги', 500);
            }
            
            sendSuccessResponse([], 'Услуга успешно удалена');
        });
    }
} 