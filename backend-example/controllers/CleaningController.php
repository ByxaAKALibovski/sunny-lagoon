<?php
/**
 * Контроллер для видов уборки
 */
class CleaningController
{
    /**
     * Получение всех видов уборки
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getAll($data)
    {
        $db = Database::getInstance();
        $cleanings = $db->fetchAll("
            SELECT c.*, tc.title as type_cleaning_title, tc.price as type_cleaning_price
            FROM cleaning c
            LEFT JOIN type_cleaning tc ON c.id_type_cleaning = tc.id_type_cleaning
            ORDER BY c.id_cleaning ASC
        ");
        
        sendSuccessResponse([
            'cleaning' => $cleanings
        ], 'Список видов уборки получен');
    }
    
    /**
     * Создание нового вида уборки (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function create($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверка данных
            $validation = validateData($data, [
                'id_type_cleaning' => 'required|numeric',
                'title' => 'required|min:3'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            $db = Database::getInstance();
            
            // Проверка существования типа уборки
            $typeCleaning = $db->fetch("SELECT id_type_cleaning FROM type_cleaning WHERE id_type_cleaning = ?", [$data['id_type_cleaning']]);
            
            if (!$typeCleaning) {
                sendErrorResponse('Указанный тип уборки не существует', 400);
            }
            
            // Подготовка данных для создания
            $cleaningData = [
                'id_type_cleaning' => $data['id_type_cleaning'],
                'title' => $data['title'],
                'image_link' => null
            ];
            
            // Обработка загруженного изображения, если оно есть
            if (isset($data['files']) && isset($data['files']['image'])) {
                $uploadDir = 'uploads/cleaning/';
                $imagePath = handleUploadedFile($data['files'], 'image', $uploadDir);
                
                if ($imagePath) {
                    $cleaningData['image_link'] = str_replace(UPLOAD_DIR, '', $imagePath);
                }
            }
            
            // Создание вида уборки
            $cleaningId = $db->insert('cleaning', $cleaningData);
            
            if (!$cleaningId) {
                sendErrorResponse('Ошибка при создании вида уборки', 500);
            }
            
            // Получение данных созданного вида уборки
            $cleaning = $db->fetch("
                SELECT c.*, tc.title as type_cleaning_title
                FROM cleaning c
                LEFT JOIN type_cleaning tc ON c.id_type_cleaning = tc.id_type_cleaning
                WHERE c.id_cleaning = ?
            ", [$cleaningId]);
            
            sendSuccessResponse([
                'cleaning' => $cleaning
            ], 'Вид уборки успешно создан');
        });
    }
    
    /**
     * Обновление вида уборки (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function update($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор вида уборки
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор вида уборки не указан', 400);
            }
            
            $cleaningId = $data['route_params']['id'];
            
            // Проверка данных
            $validation = validateData($data, [
                'id_type_cleaning' => 'numeric',
                'title' => 'min:3'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            $db = Database::getInstance();
            
            // Проверка существования вида уборки
            $cleaning = $db->fetch("SELECT * FROM cleaning WHERE id_cleaning = ?", [$cleaningId]);
            
            if (!$cleaning) {
                sendErrorResponse('Вид уборки не найден', 404);
            }
            
            // Подготовка данных для обновления
            $updateData = [];
            
            if (isset($data['id_type_cleaning'])) {
                // Проверка существования типа уборки
                $typeCleaning = $db->fetch("SELECT id_type_cleaning FROM type_cleaning WHERE id_type_cleaning = ?", [$data['id_type_cleaning']]);
                
                if (!$typeCleaning) {
                    sendErrorResponse('Указанный тип уборки не существует', 400);
                }
                
                $updateData['id_type_cleaning'] = $data['id_type_cleaning'];
            }
            
            if (isset($data['title'])) {
                $updateData['title'] = $data['title'];
            }
            
            // Обработка загруженного изображения, если оно есть
            if (isset($data['files']) && isset($data['files']['image'])) {
                $uploadDir = 'uploads/cleaning/';
                $imagePath = handleUploadedFile($data['files'], 'image', $uploadDir);
                
                if ($imagePath) {
                    $updateData['image_link'] = str_replace(UPLOAD_DIR, '', $imagePath);
                    
                    // Удаление старого изображения, если оно существует
                    if (!empty($cleaning['image_link'])) {
                        $oldImagePath = UPLOAD_DIR . $cleaning['image_link'];
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                }
            }
            
            // Если нет данных для обновления
            if (empty($updateData)) {
                sendErrorResponse('Нет данных для обновления', 400);
            }
            
            // Обновление вида уборки
            $result = $db->update('cleaning', $updateData, 'id_cleaning = ?', [$cleaningId]);
            
            if (!$result) {
                sendErrorResponse('Ошибка при обновлении вида уборки', 500);
            }
            
            // Получение обновленных данных вида уборки
            $updatedCleaning = $db->fetch("
                SELECT c.*, tc.title as type_cleaning_title
                FROM cleaning c
                LEFT JOIN type_cleaning tc ON c.id_type_cleaning = tc.id_type_cleaning
                WHERE c.id_cleaning = ?
            ", [$cleaningId]);
            
            sendSuccessResponse([
                'cleaning' => $updatedCleaning
            ], 'Вид уборки успешно обновлен');
        });
    }
    
    /**
     * Удаление вида уборки (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function delete($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор вида уборки
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор вида уборки не указан', 400);
            }
            
            $cleaningId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования вида уборки
            $cleaning = $db->fetch("SELECT * FROM cleaning WHERE id_cleaning = ?", [$cleaningId]);
            
            if (!$cleaning) {
                sendErrorResponse('Вид уборки не найден', 404);
            }
            
            // Удаление изображения, если оно существует
            if (!empty($cleaning['image_link'])) {
                $imagePath = UPLOAD_DIR . $cleaning['image_link'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Удаление вида уборки
            $result = $db->delete('cleaning', 'id_cleaning = ?', [$cleaningId]);
            
            if (!$result) {
                sendErrorResponse('Ошибка при удалении вида уборки', 500);
            }
            
            sendSuccessResponse([], 'Вид уборки успешно удален');
        });
    }
} 