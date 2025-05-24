<?php
/**
 * Контроллер для развлечений
 */
class GaietyController
{
    /**
     * Получение всех развлечений
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getAll($data)
    {
        $db = Database::getInstance();
        $gaieties = $db->fetchAll("SELECT * FROM gaiety ORDER BY id_gaiety ASC");
        
        sendSuccessResponse([
            'gaieties' => $gaieties
        ], 'Список развлечений получен');
    }
    
    /**
     * Создание нового развлечения (только для администраторов)
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
                'description' => 'required|min:10'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Подготовка данных для создания
            $gaietyData = [
                'title' => $data['title'],
                'description' => $data['description'],
                'image_link' => null
            ];
            
            // Обработка загруженного изображения, если оно есть
            if (isset($data['files']) && isset($data['files']['image'])) {
                $uploadDir = 'uploads/gaiety/';
                $imagePath = handleUploadedFile($data['files'], 'image', $uploadDir);
                
                if ($imagePath) {
                    $gaietyData['image_link'] = str_replace(UPLOAD_DIR, '', $imagePath);
                }
            }
            
            // Создание развлечения
            $db = Database::getInstance();
            $gaietyId = $db->insert('gaiety', $gaietyData);
            
            if (!$gaietyId) {
                sendErrorResponse('Ошибка при создании развлечения', 500);
            }
            
            // Получение данных созданного развлечения
            $gaiety = $db->fetch("SELECT * FROM gaiety WHERE id_gaiety = ?", [$gaietyId]);
            
            sendSuccessResponse([
                'gaiety' => $gaiety
            ], 'Развлечение успешно создано');
        });
    }
    
    /**
     * Обновление развлечения (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function update($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор развлечения
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор развлечения не указан', 400);
            }
            
            $gaietyId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования развлечения
            $gaiety = $db->fetch("SELECT * FROM gaiety WHERE id_gaiety = ?", [$gaietyId]);
            if (!$gaiety) {
                sendErrorResponse('Развлечение не найдено', 404);
            }
            
            // Проверка данных
            $validation = validateData($data, [
                'title' => 'required|min:3',
                'description' => 'required|min:10'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Подготовка данных для обновления
            $gaietyData = [
                'title' => $data['title'],
                'description' => $data['description']
            ];
            
            // Обработка загруженного изображения, если оно есть
            if (isset($data['files']) && isset($data['files']['image'])) {
                $uploadDir = 'uploads/gaiety/';
                $imagePath = handleUploadedFile($data['files'], 'image', $uploadDir);
                
                if ($imagePath) {
                    $gaietyData['image_link'] = str_replace(UPLOAD_DIR, '', $imagePath);
                    
                    // Удаление старого изображения
                    if ($gaiety['image_link']) {
                        @unlink(UPLOAD_DIR . $gaiety['image_link']);
                    }
                }
            }
            
            // Обновление развлечения
            if (!$db->update('gaiety', $gaietyData, 'id_gaiety = ?', [$gaietyId])) {
                sendErrorResponse('Ошибка при обновлении развлечения', 500);
            }
            
            // Получение обновленных данных развлечения
            $updatedGaiety = $db->fetch("SELECT * FROM gaiety WHERE id_gaiety = ?", [$gaietyId]);
            
            sendSuccessResponse([
                'gaiety' => $updatedGaiety
            ], 'Развлечение успешно обновлено');
        });
    }
    
    /**
     * Удаление развлечения (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function delete($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор развлечения
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор развлечения не указан', 400);
            }
            
            $gaietyId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования развлечения
            $gaiety = $db->fetch("SELECT * FROM gaiety WHERE id_gaiety = ?", [$gaietyId]);
            if (!$gaiety) {
                sendErrorResponse('Развлечение не найдено', 404);
            }
            
            // Удаление изображения развлечения
            if ($gaiety['image_link']) {
                @unlink(UPLOAD_DIR . $gaiety['image_link']);
            }
            
            // Удаление развлечения
            if (!$db->delete('gaiety', 'id_gaiety = ?', [$gaietyId])) {
                sendErrorResponse('Ошибка при удалении развлечения', 500);
            }
            
            sendSuccessResponse([], 'Развлечение успешно удалено');
        });
    }
} 