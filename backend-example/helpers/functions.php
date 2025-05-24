<?php
/**
 * Вспомогательные функции для API
 */

/**
 * Получение входных данных запроса
 * 
 * @return array
 */
function getRequestData() {
    $requestData = [];
    
    // Получение данных из разных типов запросов
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $requestData = $_GET;
            break;
            
        case 'POST':
            // Проверяем тип контента
            if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                // Если JSON данные
                $input = file_get_contents('php://input');
                $requestData = json_decode($input, true);
            } else {
                // Если form-data
                $requestData = $_POST;
                
                // Обработка файлов, если они есть
                if (!empty($_FILES)) {
                    $requestData['files'] = $_FILES;
                }
            }
            break;
            
        case 'PUT':
            // Обработка PUT запросов с form-data
            if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false) {
                // Получение данных из заголовка Content-Type
                $boundary = substr($_SERVER['CONTENT_TYPE'], strpos($_SERVER['CONTENT_TYPE'], 'boundary=') + 9);
                
                // Получение тела запроса
                $body = file_get_contents('php://input');
                
                // Парсинг multipart/form-data
                $formData = parseFormData($body, $boundary);
                
                $requestData = $formData['params'];
                if (!empty($formData['files'])) {
                    $requestData['files'] = $formData['files'];
                }
            } else if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                // Если JSON данные
                $input = file_get_contents('php://input');
                $requestData = json_decode($input, true);
            } else {
                // Парсинг обычных PUT данных
                parse_str(file_get_contents('php://input'), $requestData);
            }
            break;
            
        case 'DELETE':
            // Для DELETE запросов
            if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $input = file_get_contents('php://input');
                $requestData = json_decode($input, true);
            } else {
                parse_str(file_get_contents('php://input'), $requestData);
            }
            break;
    }
    
    return $requestData ?: [];
}

/**
 * Парсинг multipart/form-data для PUT запросов
 * 
 * @param string $rawInput Сырые данные запроса
 * @param string $boundary Граница между данными
 * @return array Распарсенные данные и файлы
 */
function parseFormData($rawInput, $boundary) {
    $result = [
        'params' => [],
        'files' => []
    ];
    
    // Разбиваем на части по границе
    $parts = array_slice(explode('--' . $boundary, $rawInput), 1);
    
    foreach ($parts as $part) {
        // Пропускаем завершающую границу
        if (strpos($part, '--') === 0) {
            continue;
        }
        
        // Разделяем заголовки и содержимое
        list($rawHeaders, $content) = explode("\r\n\r\n", $part, 2);
        
        // Парсинг заголовков
        $headers = [];
        foreach (explode("\r\n", $rawHeaders) as $header) {
            if (strpos($header, ':') !== false) {
                list($name, $value) = explode(':', $header, 2);
                $headers[strtolower(trim($name))] = trim($value);
            }
        }
        
        // Ищем название поля
        $contentDisposition = isset($headers['content-disposition']) ? $headers['content-disposition'] : '';
        preg_match('/name=\"([^\"]+)\"/', $contentDisposition, $matches);
        
        if (isset($matches[1])) {
            $fieldName = $matches[1];
            
            // Ищем имя файла, если это файл
            $isFile = preg_match('/filename=\"([^\"]+)\"/', $contentDisposition, $filenameMatches);
            
            if ($isFile && isset($filenameMatches[1])) {
                // Обработка файла
                $filename = $filenameMatches[1];
                $content = substr($content, 0, -2); // Удаляем \r\n в конце
                
                $contentType = isset($headers['content-type']) ? $headers['content-type'] : 'application/octet-stream';
                
                $result['files'][$fieldName] = [
                    'name' => $filename,
                    'type' => $contentType,
                    'tmp_name' => saveTemporaryFile($content),
                    'error' => 0,
                    'size' => strlen($content)
                ];
            } else {
                // Обработка обычных полей
                $content = substr($content, 0, -2); // Удаляем \r\n в конце
                $result['params'][$fieldName] = $content;
            }
        }
    }
    
    return $result;
}

/**
 * Сохранение временного файла
 * 
 * @param string $content Содержимое файла
 * @return string Путь к временному файлу
 */
function saveTemporaryFile($content) {
    $tmpFile = tempnam(sys_get_temp_dir(), 'upload_');
    file_put_contents($tmpFile, $content);
    return $tmpFile;
}

/**
 * Обработка загруженных файлов
 * 
 * @param array $files Массив с файлами
 * @param string $fieldName Имя поля с файлом
 * @param string $customDir Пользовательская директория для сохранения файла
 * @return string|boolean Путь к сохраненному файлу или false в случае ошибки
 */
function handleUploadedFile($files, $fieldName, $customDir = '') {
    if (empty($files) || !isset($files[$fieldName])) {
        return false;
    }
    
    $file = $files[$fieldName];
    
    // Проверка на ошибки загрузки
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Проверка типа файла
    if (!in_array($file['type'], ALLOWED_FILE_TYPES)) {
        return false;
    }
    
    // Проверка размера файла
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    // Определение директории для сохранения
    $uploadDir = UPLOAD_DIR;
    if (!empty($customDir)) {
        $uploadDir .= rtrim($customDir, '/') . '/';
    }
    
    // Создание директории, если не существует
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            return false;
        }
    }
    
    // Генерация уникального имени файла
    $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = uniqid() . '.' . $fileExt;
    $filePath = $uploadDir . $fileName;
    
    // Сохранение файла
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return $filePath;
    }
    
    return false;
}

/**
 * Отправка JSON ответа
 * 
 * @param array $data Данные для ответа
 * @param int $statusCode HTTP код статуса
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Отправка JSON ответа с ошибкой
 * 
 * @param string $message Сообщение об ошибке
 * @param int $statusCode HTTP код статуса
 */
function sendErrorResponse($message, $statusCode = 400) {
    sendJsonResponse([
        'success' => false,
        'error' => $message
    ], $statusCode);
}

/**
 * Отправка JSON ответа с успехом
 * 
 * @param array $data Данные для ответа
 * @param string $message Сообщение об успехе
 */
function sendSuccessResponse($data = [], $message = 'Операция выполнена успешно') {
    sendJsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

/**
 * Валидация данных запроса
 * 
 * @param array $data Данные для проверки
 * @param array $rules Правила проверки
 * @return array|true Массив ошибок или true, если проверка пройдена
 */
function validateData($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        // Проверка обязательных полей
        if (strpos($rule, 'required') !== false && (!isset($data[$field]) || empty($data[$field]))) {
            $errors[$field] = "Поле $field является обязательным";
            continue;
        }
        
        // Если поле не передано и не обязательно, пропускаем его
        if (!isset($data[$field]) || $data[$field] === '') {
            continue;
        }
        
        // Валидация email
        if (strpos($rule, 'email') !== false && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
            $errors[$field] = "Поле $field должно быть корректным email адресом";
        }
        
        // Валидация числа
        if (strpos($rule, 'numeric') !== false && !is_numeric($data[$field])) {
            $errors[$field] = "Поле $field должно быть числом";
        }
        
        // Валидация минимальной длины
        if (preg_match('/min:(\d+)/', $rule, $matches)) {
            $min = (int) $matches[1];
            if (strlen($data[$field]) < $min) {
                $errors[$field] = "Поле $field должно содержать не менее $min символов";
            }
        }
        
        // Валидация телефона (простая проверка)
        if (strpos($rule, 'phone') !== false) {
            if (!preg_match('/^[+]?[0-9()\-\s]{7,20}$/', $data[$field])) {
                $errors[$field] = "Поле $field должно быть корректным номером телефона";
            }
        }
    }
    
    return empty($errors) ? true : $errors;
}

/**
 * Получение URL API
 * 
 * @return string URL API
 */
function getApiUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return "$protocol://$host$uri";
}

/**
 * Нормализация строки URL (удаление начальных и конечных слешей)
 * 
 * @param string $url Строка URL
 * @return string Нормализованная строка
 */
function normalizeUrl($url) {
    return trim($url, '/');
}

/**
 * Генерация случайного строкового токена
 * 
 * @param int $length Длина токена
 * @return string Токен
 */
function generateRandomToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Очистка входящих данных для безопасного использования
 * 
 * @param mixed $data Входящие данные
 * @return mixed Очищенные данные
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitizeInput($value);
        }
    } else {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    return $data;
} 