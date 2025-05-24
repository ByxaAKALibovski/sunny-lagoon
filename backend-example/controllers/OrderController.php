<?php
/**
 * Контроллер для заказов
 */
class OrderController
{
    /**
     * Получение всех заказов (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getAll($data)
    {
        adminMiddleware(function($user) {
            $db = Database::getInstance();
            
            // Запрос на получение заказов с информацией о типе уборки
            $orders = $db->fetchAll("
                SELECT o.*, tc.title as type_cleaning_title
                FROM orders o
                LEFT JOIN type_cleaning tc ON o.id_type_cleaning = tc.id_type_cleaning
                ORDER BY o.created_at DESC
            ");
            
            // Добавляем информацию о дополнительных услугах
            foreach ($orders as &$order) {
                $order['services'] = $db->fetchAll("
                    SELECT s.id_services, s.title, s.price
                    FROM orders_services os
                    JOIN services s ON os.id_services = s.id_services
                    WHERE os.id_orders = ?
                ", [$order['id_orders']]);
            }
            
            sendSuccessResponse([
                'orders' => $orders
            ], 'Список заказов получен');
        });
    }
    
    /**
     * Получение информации о конкретном заказе
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getOne($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор заказа
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор заказа не указан', 400);
            }
            
            $orderId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Запрос на получение информации о заказе
            $order = $db->fetch("
                SELECT o.*, tc.title as type_cleaning_title, tc.description as type_cleaning_description
                FROM orders o
                LEFT JOIN type_cleaning tc ON o.id_type_cleaning = tc.id_type_cleaning
                WHERE o.id_orders = ?
            ", [$orderId]);
            
            if (!$order) {
                sendErrorResponse('Заказ не найден', 404);
            }
            
            // Получение информации о дополнительных услугах заказа
            $order['services'] = $db->fetchAll("
                SELECT s.id_services, s.title, s.price, s.comment
                FROM orders_services os
                JOIN services s ON os.id_services = s.id_services
                WHERE os.id_orders = ?
            ", [$orderId]);
            
            sendSuccessResponse([
                'order' => $order
            ], 'Информация о заказе получена');
        });
    }
    
    /**
     * Обновление информации о заказе (статус)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function update($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор заказа
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор заказа не указан', 400);
            }
            
            $orderId = $data['route_params']['id'];
            
            // Проверка данных
            $validation = validateData($data, [
                'status' => 'required|numeric'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Проверка корректности статуса (0-3)
            if (!in_array($data['status'], [0, 1, 2, 3])) {
                sendErrorResponse('Некорректный статус заказа. Допустимые значения: 0, 1, 2, 3', 400);
            }
            
            $db = Database::getInstance();
            
            // Проверка существования заказа
            $order = $db->fetch("SELECT id_orders FROM orders WHERE id_orders = ?", [$orderId]);
            
            if (!$order) {
                sendErrorResponse('Заказ не найден', 404);
            }
            
            // Обновление статуса заказа
            $result = $db->update('orders', ['status' => $data['status']], 'id_orders = ?', [$orderId]);
            
            if (!$result) {
                sendErrorResponse('Ошибка при обновлении статуса заказа', 500);
            }
            
            // Получение обновленной информации о заказе
            $updatedOrder = $db->fetch("SELECT * FROM orders WHERE id_orders = ?", [$orderId]);
            
            sendSuccessResponse([
                'order' => $updatedOrder
            ], 'Статус заказа успешно обновлен');
        });
    }
    
    /**
     * Создание нового заказа (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function create($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверка данных
            $validation = validateData($data, [
                'name' => 'required|min:3',
                'phone' => 'required|phone',
                'email' => 'required|email',
                'id_type_cleaning' => 'required|numeric',
                'room_area' => 'required|numeric',
                'address' => 'required|min:5',
                'date' => 'required',
                'time' => 'required'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            $db = Database::getInstance();
            
            // Проверка существования типа уборки
            $typeCleaning = $db->fetch("SELECT * FROM type_cleaning WHERE id_type_cleaning = ?", [$data['id_type_cleaning']]);
            
            if (!$typeCleaning) {
                sendErrorResponse('Указанный тип уборки не существует', 400);
            }
            
            // Расчет базовой стоимости заказа (площадь * цену типа уборки)
            $basePrice = $data['room_area'] * $typeCleaning['price'];
            $totalPrice = $basePrice;
            
            // Создание заказа
            $orderData = [
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'id_type_cleaning' => $data['id_type_cleaning'],
                'room_area' => $data['room_area'],
                'address' => $data['address'],
                'date' => $data['date'],
                'time' => $data['time'],
                'price' => $totalPrice,
                'status' => 1 // Создан
            ];
            
            $orderId = $db->insert('orders', $orderData);
            
            if (!$orderId) {
                sendErrorResponse('Ошибка при создании заказа', 500);
            }
            
            // Обработка дополнительных услуг, если они указаны
            $additionalServices = isset($data['services']) ? $data['services'] : [];
            
            if (!empty($additionalServices)) {
                // Получение информации о всех выбранных услугах
                $serviceIds = array_map('intval', $additionalServices);
                $placeholders = implode(',', array_fill(0, count($serviceIds), '?'));
                
                $services = $db->fetchAll("
                    SELECT id_services, price FROM services 
                    WHERE id_services IN ($placeholders)
                ", $serviceIds);
                
                // Добавление услуг к заказу и обновление общей стоимости
                foreach ($services as $service) {
                    // Добавление связи заказа и услуги
                    $db->insert('orders_services', [
                        'id_orders' => $orderId,
                        'id_services' => $service['id_services']
                    ]);
                    
                    // Увеличение общей стоимости
                    $totalPrice += $service['price'];
                }
                
                // Обновление общей стоимости заказа
                $db->update('orders', ['price' => $totalPrice], 'id_orders = ?', [$orderId]);
            }
            
            // Получение данных созданного заказа
            $order = $db->fetch("SELECT * FROM orders WHERE id_orders = ?", [$orderId]);
            
            sendSuccessResponse([
                'order' => $order
            ], 'Заказ успешно создан');
        });
    }
    
    /**
     * Получение заказов текущего пользователя
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getMyOrders($data)
    {
        authMiddleware(function($user) {
            $db = Database::getInstance();
            
            // Получение заказов пользователя по email
            $orders = $db->fetchAll("
                SELECT o.*, tc.title as type_cleaning_title
                FROM orders o
                LEFT JOIN type_cleaning tc ON o.id_type_cleaning = tc.id_type_cleaning
                WHERE o.email = ?
                ORDER BY o.created_at DESC
            ", [$user['email']]);
            
            // Добавляем информацию о дополнительных услугах
            foreach ($orders as &$order) {
                $order['services'] = $db->fetchAll("
                    SELECT s.id_services, s.title, s.price
                    FROM orders_services os
                    JOIN services s ON os.id_services = s.id_services
                    WHERE os.id_orders = ?
                ", [$order['id_orders']]);
            }
            
            sendSuccessResponse([
                'orders' => $orders
            ], 'Список заказов получен');
        });
    }
} 