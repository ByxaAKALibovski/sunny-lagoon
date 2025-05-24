<?php
/**
 * Контроллер для домов
 */
class HomeController
{
    /**
     * Получение всех домов
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getAll($data)
    {
        $db = Database::getInstance();
        $homes = $db->fetchAll("
            SELECT h.*, c.title as category_title, 
                   GROUP_CONCAT(hi.image_link) as images
            FROM home h
            LEFT JOIN category c ON h.id_category = c.id_category
            LEFT JOIN home_images hi ON h.id_home = hi.id_home
            GROUP BY h.id_home
            ORDER BY h.id_home ASC
        ");
        
        // Преобразование строки с изображениями в массив
        foreach ($homes as &$home) {
            $home['images'] = $home['images'] ? explode(',', $home['images']) : [];
        }
        
        sendSuccessResponse([
            'homes' => $homes
        ], 'Список домов получен');
    }
    
    /**
     * Получение одного дома по ID
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getOne($data)
    {
        // Проверяем, передан ли идентификатор дома
        if (!isset($data['route_params']['id'])) {
            sendErrorResponse('Идентификатор дома не указан', 400);
        }
        
        $homeId = $data['route_params']['id'];
        $db = Database::getInstance();
        
        // Получение данных дома
        $home = $db->fetch("
            SELECT h.*, c.title as category_title,
                   GROUP_CONCAT(hi.image_link) as images
            FROM home h
            LEFT JOIN category c ON h.id_category = c.id_category
            LEFT JOIN home_images hi ON h.id_home = hi.id_home
            WHERE h.id_home = ?
            GROUP BY h.id_home
        ", [$homeId]);
        
        if (!$home) {
            sendErrorResponse('Дом не найден', 404);
        }
        
        // Преобразование строки с изображениями в массив
        $home['images'] = $home['images'] ? explode(',', $home['images']) : [];
        
        sendSuccessResponse([
            'home' => $home
        ], 'Дом получен');
    }
    
    /**
     * Создание нового дома (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function create($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверка данных
            $validation = validateData($data, [
                'id_category' => 'required|numeric',
                'title' => 'required|min:3',
                'capacity' => 'required|numeric',
                'description' => 'required|min:10',
                'price' => 'required|numeric'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            $db = Database::getInstance();
            
            // Проверка существования категории
            $category = $db->fetch("SELECT id_category FROM category WHERE id_category = ?", [$data['id_category']]);
            if (!$category) {
                sendErrorResponse('Указанная категория не существует', 400);
            }
            
            // Создание дома
            $homeData = [
                'id_category' => $data['id_category'],
                'title' => $data['title'],
                'capacity' => $data['capacity'],
                'description' => $data['description'],
                'price' => $data['price']
            ];
            
            $homeId = $db->insert('home', $homeData);
            
            if (!$homeId) {
                sendErrorResponse('Ошибка при создании дома', 500);
            }
            
            // Обработка загруженных изображений
            if (isset($data['files']) && isset($data['files']['images']) && is_array($data['files']['images'])) {
                $uploadDir = 'uploads/home/';
                
                foreach ($data['files']['images'] as $image) {
                    $imagePath = handleUploadedFile(['image' => $image], 'image', $uploadDir);
                    
                    if ($imagePath) {
                        $imageData = [
                            'id_home' => $homeId,
                            'image_link' => str_replace(UPLOAD_DIR, '', $imagePath)
                        ];
                        $db->insert('home_images', $imageData);
                    }
                }
            }
            
            // Получение данных созданного дома
            $home = $db->fetch("
                SELECT h.*, c.title as category_title,
                       GROUP_CONCAT(hi.image_link) as images
                FROM home h
                LEFT JOIN category c ON h.id_category = c.id_category
                LEFT JOIN home_images hi ON h.id_home = hi.id_home
                WHERE h.id_home = ?
                GROUP BY h.id_home
            ", [$homeId]);
            
            // Преобразование строки с изображениями в массив
            $home['images'] = $home['images'] ? explode(',', $home['images']) : [];
            
            sendSuccessResponse([
                'home' => $home
            ], 'Дом успешно создан');
        });
    }
    
    /**
     * Обновление дома (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function update($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор дома
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор дома не указан', 400);
            }
            
            $homeId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования дома
            $home = $db->fetch("SELECT * FROM home WHERE id_home = ?", [$homeId]);
            if (!$home) {
                sendErrorResponse('Дом не найден', 404);
            }
            
            // Проверка данных
            $validation = validateData($data, [
                'id_category' => 'required|numeric',
                'title' => 'required|min:3',
                'capacity' => 'required|numeric',
                'description' => 'required|min:10',
                'price' => 'required|numeric'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Проверка существования категории
            $category = $db->fetch("SELECT id_category FROM category WHERE id_category = ?", [$data['id_category']]);
            if (!$category) {
                sendErrorResponse('Указанная категория не существует', 400);
            }
            
            // Обновление данных дома
            $homeData = [
                'id_category' => $data['id_category'],
                'title' => $data['title'],
                'capacity' => $data['capacity'],
                'description' => $data['description'],
                'price' => $data['price']
            ];
            
            if (!$db->update('home', $homeData, 'id_home = ?', [$homeId])) {
                sendErrorResponse('Ошибка при обновлении дома', 500);
            }
            
            // Обработка загруженных изображений
            if (isset($data['files']) && isset($data['files']['images']) && is_array($data['files']['images'])) {
                $uploadDir = 'uploads/home/';
                
                // Получение текущих изображений
                $currentImages = $db->fetchAll("SELECT * FROM home_images WHERE id_home = ?", [$homeId]);
                
                // Удаление старых изображений
                foreach ($currentImages as $image) {
                    @unlink(UPLOAD_DIR . $image['image_link']);
                }
                $db->delete('home_images', 'id_home = ?', [$homeId]);
                
                // Загрузка новых изображений
                foreach ($data['files']['images'] as $image) {
                    $imagePath = handleUploadedFile(['image' => $image], 'image', $uploadDir);
                    
                    if ($imagePath) {
                        $imageData = [
                            'id_home' => $homeId,
                            'image_link' => str_replace(UPLOAD_DIR, '', $imagePath)
                        ];
                        $db->insert('home_images', $imageData);
                    }
                }
            }
            
            // Получение обновленных данных дома
            $updatedHome = $db->fetch("
                SELECT h.*, c.title as category_title,
                       GROUP_CONCAT(hi.image_link) as images
                FROM home h
                LEFT JOIN category c ON h.id_category = c.id_category
                LEFT JOIN home_images hi ON h.id_home = hi.id_home
                WHERE h.id_home = ?
                GROUP BY h.id_home
            ", [$homeId]);
            
            // Преобразование строки с изображениями в массив
            $updatedHome['images'] = $updatedHome['images'] ? explode(',', $updatedHome['images']) : [];
            
            sendSuccessResponse([
                'home' => $updatedHome
            ], 'Дом успешно обновлен');
        });
    }
    
    /**
     * Удаление дома (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function delete($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор дома
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор дома не указан', 400);
            }
            
            $homeId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования дома
            $home = $db->fetch("SELECT * FROM home WHERE id_home = ?", [$homeId]);
            if (!$home) {
                sendErrorResponse('Дом не найден', 404);
            }
            
            // Получение и удаление изображений дома
            $images = $db->fetchAll("SELECT * FROM home_images WHERE id_home = ?", [$homeId]);
            foreach ($images as $image) {
                @unlink(UPLOAD_DIR . $image['image_link']);
            }
            
            // Удаление дома (каскадное удаление изображений через внешний ключ)
            if (!$db->delete('home', 'id_home = ?', [$homeId])) {
                sendErrorResponse('Ошибка при удалении дома', 500);
            }
            
            sendSuccessResponse([], 'Дом успешно удален');
        });
    }
} 