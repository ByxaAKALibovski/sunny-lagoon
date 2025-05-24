<?php
/**
 * Класс маршрутизатора
 */
class Router
{
    private $routes = [];
    private $basePath = '';
    
    /**
     * Конструктор класса
     * 
     * @param string $basePath Базовый путь для маршрутов
     */
    public function __construct($basePath = '')
    {
        $this->basePath = $basePath;
    }
    
    /**
     * Добавление GET маршрута
     * 
     * @param string $path Путь маршрута
     * @param callable $callback Функция обратного вызова
     * @return void
     */
    public function get($path, $callback)
    {
        $this->addRoute('GET', $path, $callback);
    }
    
    /**
     * Добавление POST маршрута
     * 
     * @param string $path Путь маршрута
     * @param callable $callback Функция обратного вызова
     * @return void
     */
    public function post($path, $callback)
    {
        $this->addRoute('POST', $path, $callback);
    }
    
    /**
     * Добавление PUT маршрута
     * 
     * @param string $path Путь маршрута
     * @param callable $callback Функция обратного вызова
     * @return void
     */
    public function put($path, $callback)
    {
        $this->addRoute('PUT', $path, $callback);
    }
    
    /**
     * Добавление DELETE маршрута
     * 
     * @param string $path Путь маршрута
     * @param callable $callback Функция обратного вызова
     * @return void
     */
    public function delete($path, $callback)
    {
        $this->addRoute('DELETE', $path, $callback);
    }
    
    /**
     * Добавление маршрута
     * 
     * @param string $method HTTP метод
     * @param string $path Путь маршрута
     * @param callable $callback Функция обратного вызова
     * @return void
     */
    private function addRoute($method, $path, $callback)
    {
        // Нормализация пути
        $path = normalizeUrl($path);
        $path = $this->basePath . '/' . $path;
        $path = normalizeUrl($path);
        
        // Преобразование параметров пути в регулярные выражения
        $pattern = str_replace('/', '\/', $path);
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^\/]+)', $pattern);
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => '/^' . $pattern . '$/i',
            'callback' => $callback
        ];
    }
    
    /**
     * Запуск маршрутизатора
     * 
     * @return void
     */
    public function run()
    {
        // Получение пути запроса
        $requestUri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Удаление GET-параметров
        $uri = parse_url($requestUri, PHP_URL_PATH);
        
        // Удаление базового пути скрипта
        $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptPath !== '/') {
            $uri = substr($uri, strlen($scriptPath));
        }
        
        $uri = normalizeUrl($uri);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Удаление полного совпадения из массива совпадений
                array_shift($matches);
                
                // Определение параметров маршрута из URI
                $params = [];
                if (preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $route['path'], $paramNames)) {
                    // Создаем ассоциативный массив параметров
                    $paramNames = $paramNames[1];
                    for ($i = 0; $i < count($paramNames); $i++) {
                        if (isset($matches[$i])) {
                            $params[$paramNames[$i]] = $matches[$i];
                        }
                    }
                }
                
                // Выполнение функции обратного вызова маршрута
                $requestData = getRequestData();
                
                // Добавляем параметры маршрута к данным запроса
                $requestData['route_params'] = $params;
                
                call_user_func($route['callback'], $requestData);
                return;
            }
        }
        
        // Если маршрут не найден
        header('HTTP/1.1 404 Not Found');
        sendErrorResponse('Маршрут не найден', 404);
    }
} 