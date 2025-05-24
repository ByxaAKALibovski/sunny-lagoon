<?php
/**
 * Контроллер для отзывов
 */
class ReviewController
{
    /**
     * Получение всех отзывов
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function getAll($data)
    {
        $db = Database::getInstance();
        $reviews = $db->fetchAll("SELECT * FROM reviews ORDER BY created_at DESC");
        
        sendSuccessResponse([
            'reviews' => $reviews
        ], 'Список отзывов получен');
    }
    
    /**
     * Создание нового отзыва
     * 
     * @param array $data Данные запроса
     * @return void
     */
    public static function create($data)
    {
        // Проверка данных
        $validation = validateData($data, [
            'name' => 'required|min:3',
            'text' => 'required|min:10'
        ]);
        
        if ($validation !== true) {
            sendErrorResponse($validation, 400);
        }
        
        // Подготовка данных для создания
        $reviewData = [
            'name' => $data['name'],
            'text' => $data['text']
        ];
        
        // Создание отзыва
        $db = Database::getInstance();
        $reviewId = $db->insert('reviews', $reviewData);
        
        if (!$reviewId) {
            sendErrorResponse('Ошибка при создании отзыва', 500);
        }
        
        // Получение данных созданного отзыва
        $review = $db->fetch("SELECT * FROM reviews WHERE id_reviews = ?", [$reviewId]);
        
        sendSuccessResponse([
            'review' => $review
        ], 'Отзыв успешно создан');
    }
} 