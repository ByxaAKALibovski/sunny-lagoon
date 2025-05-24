<?php
/**
 * Контроллер для услуг
 */
class ServicesController
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
                'description' => 'required|min:10',
                'price' => 'required|numeric'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Создание услуги
            $serviceData = [
                'title' => $data['title'],
                'description' => $data['description'],
                'price' => $data['price']
            ];
            
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
            $db = Database::getInstance();
            
            // Проверка существования услуги
            $service = $db->fetch("SELECT * FROM services WHERE id_services = ?", [$serviceId]);
            if (!$service) {
                sendErrorResponse('Услуга не найдена', 404);
            }
            
            // Проверка данных
            $validation = validateData($data, [
                'title' => 'required|min:3',
                'description' => 'required|min:10',
                'price' => 'required|numeric'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Обновление данных услуги
            $serviceData = [
                'title' => $data['title'],
                'description' => $data['description'],
                'price' => $data['price']
            ];
            
            if (!$db->update('services', $serviceData, 'id_services = ?', [$serviceId])) {
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
            $service = $db->fetch("SELECT * FROM services WHERE id_services = ?", [$serviceId]);
            if (!$service) {
                sendErrorResponse('Услуга не найдена', 404);
            }
            
            // Удаление услуги
            if (!$db->delete('services', 'id_services = ?', [$serviceId])) {
                sendErrorResponse('Ошибка при удалении услуги', 500);
            }
            
            sendSuccessResponse([], 'Услуга успешно удалена');
        });
    }
} 