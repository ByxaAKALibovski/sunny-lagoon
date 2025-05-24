<?php
/**
 * Контроллер для заявок
 */
class ApplicationController
{
    /**
     * Создание новой заявки
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function create($data)
    {
        // Проверка данных
        $validation = validateData($data, [
            'name' => 'required|min:3',
            'room_area' => 'required|numeric',
            'phone' => 'required|phone'
        ]);
        
        if ($validation !== true) {
            sendErrorResponse($validation, 400);
        }
        
        // Подготовка данных для сохранения
        $applicationData = [
            'name' => $data['name'],
            'room_area' => $data['room_area'],
            'phone' => $data['phone']
        ];
        
        // Сохранение заявки
        $db = Database::getInstance();
        $applicationId = $db->insert('application', $applicationData);
        
        if (!$applicationId) {
            sendErrorResponse('Ошибка при создании заявки', 500);
        }
        
        // Получение данных созданной заявки
        $application = $db->fetch("SELECT * FROM application WHERE id_application = ?", [$applicationId]);
        
        sendSuccessResponse([
            'application' => $application
        ], 'Заявка успешно создана');
    }
    
    /**
     * Получение всех заявок (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getAll($data)
    {
        adminMiddleware(function($user) {
            // Получаем все заявки
            $db = Database::getInstance();
            $applications = $db->fetchAll("SELECT * FROM application ORDER BY created_at DESC");
            
            sendSuccessResponse([
                'applications' => $applications
            ], 'Список заявок получен');
        });
    }
} 