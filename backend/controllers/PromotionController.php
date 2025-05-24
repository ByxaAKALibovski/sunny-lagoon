<?php
/**
 * Контроллер для акций
 */
class PromotionController
{
    /**
     * Получение всех акций
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getAll($data)
    {
        $db = Database::getInstance();
        $promotions = $db->fetchAll("SELECT * FROM promotion ORDER BY id_promotion ASC");
        
        sendSuccessResponse([
            'promotions' => $promotions
        ], 'Список акций получен');
    }
    
    /**
     * Создание новой акции (только для администраторов)
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
            $promotionData = [
                'title' => $data['title'],
                'description' => $data['description'],
                'image_link' => null
            ];
            
            // Обработка загруженного изображения, если оно есть
            if (isset($data['files']) && isset($data['files']['image'])) {
                $uploadDir = 'uploads/promotion/';
                $imagePath = handleUploadedFile($data['files'], 'image', $uploadDir);
                
                if ($imagePath) {
                    $promotionData['image_link'] = str_replace(UPLOAD_DIR, '', $imagePath);
                }
            }
            
            // Создание акции
            $db = Database::getInstance();
            $promotionId = $db->insert('promotion', $promotionData);
            
            if (!$promotionId) {
                sendErrorResponse('Ошибка при создании акции', 500);
            }
            
            // Получение данных созданной акции
            $promotion = $db->fetch("SELECT * FROM promotion WHERE id_promotion = ?", [$promotionId]);
            
            sendSuccessResponse([
                'promotion' => $promotion
            ], 'Акция успешно создана');
        });
    }
    
    /**
     * Обновление акции (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function update($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор акции
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор акции не указан', 400);
            }
            
            $promotionId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования акции
            $promotion = $db->fetch("SELECT * FROM promotion WHERE id_promotion = ?", [$promotionId]);
            if (!$promotion) {
                sendErrorResponse('Акция не найдена', 404);
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
            $promotionData = [
                'title' => $data['title'],
                'description' => $data['description']
            ];
            
            // Обработка загруженного изображения, если оно есть
            if (isset($data['files']) && isset($data['files']['image'])) {
                $uploadDir = 'uploads/promotion/';
                $imagePath = handleUploadedFile($data['files'], 'image', $uploadDir);
                
                if ($imagePath) {
                    $promotionData['image_link'] = str_replace(UPLOAD_DIR, '', $imagePath);
                    
                    // Удаление старого изображения
                    if ($promotion['image_link']) {
                        @unlink(UPLOAD_DIR . $promotion['image_link']);
                    }
                }
            }
            
            // Обновление акции
            if (!$db->update('promotion', $promotionData, 'id_promotion = ?', [$promotionId])) {
                sendErrorResponse('Ошибка при обновлении акции', 500);
            }
            
            // Получение обновленных данных акции
            $updatedPromotion = $db->fetch("SELECT * FROM promotion WHERE id_promotion = ?", [$promotionId]);
            
            sendSuccessResponse([
                'promotion' => $updatedPromotion
            ], 'Акция успешно обновлена');
        });
    }
    
    /**
     * Удаление акции (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function delete($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор акции
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор акции не указан', 400);
            }
            
            $promotionId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования акции
            $promotion = $db->fetch("SELECT * FROM promotion WHERE id_promotion = ?", [$promotionId]);
            if (!$promotion) {
                sendErrorResponse('Акция не найдена', 404);
            }
            
            // Удаление изображения акции
            if ($promotion['image_link']) {
                @unlink(UPLOAD_DIR . $promotion['image_link']);
            }
            
            // Удаление акции
            if (!$db->delete('promotion', 'id_promotion = ?', [$promotionId])) {
                sendErrorResponse('Ошибка при удалении акции', 500);
            }
            
            sendSuccessResponse([], 'Акция успешно удалена');
        });
    }
} 