<?php
/**
 * Контроллер для статей
 */
class ArticleController
{
    /**
     * Получение всех статей
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getAll($data)
    {
        $db = Database::getInstance();
        $articles = $db->fetchAll("SELECT * FROM article ORDER BY created_at DESC");
        
        sendSuccessResponse([
            'articles' => $articles
        ], 'Список статей получен');
    }
    
    /**
     * Получение одной статьи по ID
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getOne($data)
    {
        // Проверяем, передан ли идентификатор статьи
        if (!isset($data['route_params']['id'])) {
            sendErrorResponse('Идентификатор статьи не указан', 400);
        }
        
        $articleId = $data['route_params']['id'];
        $db = Database::getInstance();
        
        // Получение статьи
        $article = $db->fetch("SELECT * FROM article WHERE id_article = ?", [$articleId]);
        
        if (!$article) {
            sendErrorResponse('Статья не найдена', 404);
        }
        
        sendSuccessResponse([
            'article' => $article
        ], 'Статья получена');
    }
    
    /**
     * Создание новой статьи (только для администраторов)
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
                'text' => 'required|min:10'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            // Подготовка данных для создания
            $articleData = [
                'title' => $data['title'],
                'text' => $data['text'],
                'image_link' => null
            ];
            
            // Обработка загруженного изображения, если оно есть
            if (isset($data['files']) && isset($data['files']['image'])) {
                $uploadDir = 'uploads/article/';
                $imagePath = handleUploadedFile($data['files'], 'image', $uploadDir);
                
                if ($imagePath) {
                    $articleData['image_link'] = str_replace(UPLOAD_DIR, '', $imagePath);
                }
            }
            
            // Создание статьи
            $db = Database::getInstance();
            $articleId = $db->insert('article', $articleData);
            
            if (!$articleId) {
                sendErrorResponse('Ошибка при создании статьи', 500);
            }
            
            // Получение данных созданной статьи
            $article = $db->fetch("SELECT * FROM article WHERE id_article = ?", [$articleId]);
            
            sendSuccessResponse([
                'article' => $article
            ], 'Статья успешно создана');
        });
    }
    
    /**
     * Обновление статьи (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function update($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор статьи
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор статьи не указан', 400);
            }
            
            $articleId = $data['route_params']['id'];
            
            // Проверка данных
            $validation = validateData($data, [
                'title' => 'min:3',
                'text' => 'min:10'
            ]);
            
            if ($validation !== true) {
                sendErrorResponse($validation, 400);
            }
            
            $db = Database::getInstance();
            
            // Проверка существования статьи
            $article = $db->fetch("SELECT * FROM article WHERE id_article = ?", [$articleId]);
            
            if (!$article) {
                sendErrorResponse('Статья не найдена', 404);
            }
            
            // Подготовка данных для обновления
            $updateData = [];
            
            if (isset($data['title'])) {
                $updateData['title'] = $data['title'];
            }
            
            if (isset($data['text'])) {
                $updateData['text'] = $data['text'];
            }
            
            // Обработка загруженного изображения, если оно есть
            if (isset($data['files']) && isset($data['files']['image'])) {
                $uploadDir = 'uploads/article/';
                $imagePath = handleUploadedFile($data['files'], 'image', $uploadDir);
                
                if ($imagePath) {
                    $updateData['image_link'] = str_replace(UPLOAD_DIR, '', $imagePath);
                    
                    // Удаление старого изображения, если оно существует
                    if (!empty($article['image_link'])) {
                        $oldImagePath = UPLOAD_DIR . $article['image_link'];
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
            
            // Обновление статьи
            $result = $db->update('article', $updateData, 'id_article = ?', [$articleId]);
            
            if (!$result) {
                sendErrorResponse('Ошибка при обновлении статьи', 500);
            }
            
            // Получение обновленных данных статьи
            $updatedArticle = $db->fetch("SELECT * FROM article WHERE id_article = ?", [$articleId]);
            
            sendSuccessResponse([
                'article' => $updatedArticle
            ], 'Статья успешно обновлена');
        });
    }
    
    /**
     * Удаление статьи (только для администраторов)
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function delete($data)
    {
        adminMiddleware(function($user) use ($data) {
            // Проверяем, передан ли идентификатор статьи
            if (!isset($data['route_params']['id'])) {
                sendErrorResponse('Идентификатор статьи не указан', 400);
            }
            
            $articleId = $data['route_params']['id'];
            $db = Database::getInstance();
            
            // Проверка существования статьи
            $article = $db->fetch("SELECT * FROM article WHERE id_article = ?", [$articleId]);
            
            if (!$article) {
                sendErrorResponse('Статья не найдена', 404);
            }
            
            // Удаление изображения, если оно существует
            if (!empty($article['image_link'])) {
                $imagePath = UPLOAD_DIR . $article['image_link'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Удаление статьи
            $result = $db->delete('article', 'id_article = ?', [$articleId]);
            
            if (!$result) {
                sendErrorResponse('Ошибка при удалении статьи', 500);
            }
            
            sendSuccessResponse([], 'Статья успешно удалена');
        });
    }
} 