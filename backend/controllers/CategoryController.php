<?php
/**
 * Контроллер для категорий
 */
class CategoryController
{
    /**
     * Получение всех категорий
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getAll($data)
    {
        $db = Database::getInstance();
        $categories = $db->fetchAll("SELECT * FROM category ORDER BY id_category ASC");
        
        sendSuccessResponse([
            'categories' => $categories
        ], 'Список категорий получен');
    }
    
    /**
     * Создание новой категории (только для администраторов)
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
                'short_title' => 'required|min:3',
                'short_title_mult' => 'required|min:3',
                'capacity' => 'required|numeric',
                'description' => 'required|min:10',
                'prev_text' => 'required|min:10'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Подготовка данных для создания
            $categoryData = [
                'title' => $data['title'],
                'short_title' => $data['short_title'],
                'short_title_mult' => $data['short_title_mult'],
                'capacity' => $data['capacity'],
                'description' => $data['description'],
                'prev_text' => $data['prev_text'],
                'image_link' => null
            ];
            
            // Обработка загруженного изображения, если оно есть
            if (isset($data['files']) && isset($data['files']['image'])) {
                $uploadDir = 'uploads/category/';
                $imagePath = handleUploadedFile($data['files'], 'image', $uploadDir);
                
                if ($imagePath) {
                    $categoryData['image_link'] = str_replace(UPLOAD_DIR, '', $imagePath);
                }
            }
            
            // Создание категории
            $db = Database::getInstance();
            $categoryId = $db->insert('category', $categoryData);
            
            if (!$categoryId) {
                sendErrorResponse('Ошибка при создании категории', 500);
            }
            
            // Получение данных созданной категории
            $category = $db->fetch("SELECT * FROM category WHERE id_category = ?", [$categoryId]);
            
            sendSuccessResponse([
                'category' => $category
            ], 'Категория успешно создана');
        });
    }
    
    /**
     * Обновление категории (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function update($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор категории
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор категории не указан', 400);
            }
            
            $categoryId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования категории
            $category = $db->fetch("SELECT * FROM category WHERE id_category = ?", [$categoryId]);
            if (!$category) {
                sendErrorResponse('Категория не найдена', 404);
            }
            
            // Проверка данных
            $validation = validateData($data, [
                'title' => 'required|min:3',
                'short_title' => 'required|min:3',
                'short_title_mult' => 'required|min:3',
                'capacity' => 'required|numeric',
                'description' => 'required|min:10',
                'prev_text' => 'required|min:10'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Подготовка данных для обновления
            $categoryData = [
                'title' => $data['title'],
                'short_title' => $data['short_title'],
                'short_title_mult' => $data['short_title_mult'],
                'capacity' => $data['capacity'],
                'description' => $data['description'],
                'prev_text' => $data['prev_text']
            ];
            
            // Обработка загруженного изображения, если оно есть
            if (isset($data['files']) && isset($data['files']['image'])) {
                $uploadDir = 'uploads/category/';
                $imagePath = handleUploadedFile($data['files'], 'image', $uploadDir);
                
                if ($imagePath) {
                    $categoryData['image_link'] = str_replace(UPLOAD_DIR, '', $imagePath);
                    
                    // Удаление старого изображения
                    if ($category['image_link']) {
                        @unlink(UPLOAD_DIR . $category['image_link']);
                    }
                }
            }
            
            // Обновление категории
            if (!$db->update('category', $categoryData, 'id_category = ?', [$categoryId])) {
                sendErrorResponse('Ошибка при обновлении категории', 500);
            }
            
            // Получение обновленных данных категории
            $updatedCategory = $db->fetch("SELECT * FROM category WHERE id_category = ?", [$categoryId]);
            
            sendSuccessResponse([
                'category' => $updatedCategory
            ], 'Категория успешно обновлена');
        });
    }
    
    /**
     * Удаление категории (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function delete($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор категории
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор категории не указан', 400);
            }
            
            $categoryId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования категории
            $category = $db->fetch("SELECT * FROM category WHERE id_category = ?", [$categoryId]);
            if (!$category) {
                sendErrorResponse('Категория не найдена', 404);
            }
            
            // Удаление изображения категории
            if ($category['image_link']) {
                @unlink(UPLOAD_DIR . $category['image_link']);
            }
            
            // Удаление категории
            if (!$db->delete('category', 'id_category = ?', [$categoryId])) {
                sendErrorResponse('Ошибка при удалении категории', 500);
            }
            
            sendSuccessResponse([], 'Категория успешно удалена');
        });
    }
} 