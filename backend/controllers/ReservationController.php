<?php
/**
 * Контроллер для бронирований
 */
class ReservationController
{
    /**
     * Получение всех бронирований (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getAll($data)
    {
        adminMiddleware(function($user) {
            $db = Database::getInstance();
            $reservations = $db->fetchAll("
                SELECT r.*, h.title as home_title, h.price as home_price
                FROM reservation r
                LEFT JOIN home h ON r.id_home = h.id_home
                ORDER BY r.created_at DESC
            ");
            
            sendSuccessResponse([
                'reservations' => $reservations
            ], 'Список бронирований получен');
        });
    }
    
    /**
     * Создание нового бронирования
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function create($data)
    {
        // Проверка данных
        $validation = validateData($data, [
            'id_home' => 'required|numeric',
            'name' => 'required|min:3',
            'phone' => 'required|phone',
            'date_enter' => 'required',
            'date_back' => 'required',
            'count_old' => 'required|numeric',
            'count_child' => 'required|numeric'
        ]);
        
        if ($validation !== true) {
            sendErrorResponse($validation, 400);
        }
        
        $db = Database::getInstance();
        
        // Проверка существования дома
        $home = $db->fetch("SELECT * FROM home WHERE id_home = ?", [$data['id_home']]);
        if (!$home) {
            sendErrorResponse('Указанный дом не существует', 400);
        }
        
        // Проверка дат
        $dateEnter = strtotime($data['date_enter']);
        $dateBack = strtotime($data['date_back']);
        
        if ($dateEnter < strtotime('today') || $dateBack <= $dateEnter) {
            sendErrorResponse('Некорректные даты бронирования', 400);
        }
        
        // Проверка количества гостей
        $totalGuests = $data['count_old'] + $data['count_child'];
        if ($totalGuests > $home['capacity']) {
            sendErrorResponse('Превышено максимальное количество гостей для данного дома', 400);
        }
        
        // Проверка доступности дома на выбранные даты
        $existingReservation = $db->fetch("
            SELECT id_reservation 
            FROM reservation 
            WHERE id_home = ? 
            AND status != 2
            AND (
                (date_enter <= ? AND date_back >= ?) OR
                (date_enter <= ? AND date_back >= ?) OR
                (date_enter >= ? AND date_back <= ?)
            )
        ", [
            $data['id_home'],
            $data['date_enter'], $data['date_enter'],
            $data['date_back'], $data['date_back'],
            $data['date_enter'], $data['date_back']
        ]);
        
        if ($existingReservation) {
            sendErrorResponse('Дом уже забронирован на выбранные даты', 400);
        }
        
        // Создание бронирования
        $reservationData = [
            'id_home' => $data['id_home'],
            'name' => $data['name'],
            'phone' => $data['phone'],
            'date_enter' => $data['date_enter'],
            'date_back' => $data['date_back'],
            'count_old' => $data['count_old'],
            'count_child' => $data['count_child'],
            'status' => 0 // Новая
        ];
        
        $reservationId = $db->insert('reservation', $reservationData);
        
        if (!$reservationId) {
            sendErrorResponse('Ошибка при создании бронирования', 500);
        }
        
        // Получение данных созданного бронирования
        $reservation = $db->fetch("
            SELECT r.*, h.title as home_title, h.price as home_price
            FROM reservation r
            LEFT JOIN home h ON r.id_home = h.id_home
            WHERE r.id_reservation = ?
        ", [$reservationId]);
        
        sendSuccessResponse([
            'reservation' => $reservation
        ], 'Бронирование успешно создано');
    }
    
    /**
     * Обновление статуса бронирования (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function update($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор бронирования
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор бронирования не указан', 400);
            }
            
            $reservationId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования бронирования
            $reservation = $db->fetch("SELECT * FROM reservation WHERE id_reservation = ?", [$reservationId]);
            if (!$reservation) {
                sendErrorResponse('Бронирование не найдено', 404);
            }
            
            // Проверка данных
            $validation = validateData($data, [
                'status' => 'required|numeric'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Проверка корректности статуса
            if (!in_array($data['status'], [0, 1, 2])) {
                sendErrorResponse('Некорректный статус бронирования', 400);
            }
            
            // Обновление статуса бронирования
            if (!$db->update('reservation', ['status' => $data['status']], 'id_reservation = ?', [$reservationId])) {
                sendErrorResponse('Ошибка при обновлении статуса бронирования', 500);
            }
            
            // Получение обновленных данных бронирования
            $updatedReservation = $db->fetch("
                SELECT r.*, h.title as home_title, h.price as home_price
                FROM reservation r
                LEFT JOIN home h ON r.id_home = h.id_home
                WHERE r.id_reservation = ?
            ", [$reservationId]);
            
            sendSuccessResponse([
                'reservation' => $updatedReservation
            ], 'Статус бронирования успешно обновлен');
        });
    }
} 