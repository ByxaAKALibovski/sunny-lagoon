<?php
/**
 * Класс маршрутизатора
 */
class Router
{
    private $routes = [];
    private $notFoundCallback;
    
    /**
     * Добавление GET маршрута
     */
    public function get($path, $callback)
    {
        $this->addRoute('GET', $path, $callback);
    }
    
    /**
     * Добавление POST маршрута
     */
    public function post($path, $callback)
    {
        $this->addRoute('POST', $path, $callback);
    }
    
    /**
     * Добавление PUT маршрута
     */
    public function put($path, $callback)
    {
        $this->addRoute('PUT', $path, $callback);
    }
    
    /**
     * Добавление DELETE маршрута
     */
    public function delete($path, $callback)
    {
        $this->addRoute('DELETE', $path, $callback);
    }
    
    /**
     * Добавление маршрута
     */
    private function addRoute($method, $path, $callback)
    {
        // Преобразование пути в регулярное выражение
        $pattern = preg_replace('/\{([^\/]+)\}/', '(?P<$1>[^\/]+)', $path);
        $pattern = "#^{$pattern}$#";
        
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'callback' => $callback
        ];
    }
    
    /**
     * Установка обработчика для несуществующих маршрутов
     */
    public function setNotFoundHandler($callback)
    {
        $this->notFoundCallback = $callback;
    }
    
    /**
     * Обработка запроса
     */
    public function handleRequest()
    {
        // Получение метода и пути запроса
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Удаление базового пути из URL
        $basePath = '/backend/';
        if (strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        $path = trim($path, '/');
        
        // Поиск подходящего маршрута
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $path, $matches)) {
                // Подготовка параметров маршрута
                $routeParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                // Подготовка данных запроса
                $data = [
                    'route_params' => $routeParams,
                    'query' => $_GET,
                    'body' => [],
                    'files' => $_FILES
                ];
                
                // Получение данных из тела запроса
                $input = file_get_contents('php://input');
                if (!empty($input)) {
                    $data['body'] = json_decode($input, true) ?? [];
                } else {
                    $data['body'] = $_POST;
                }
                
                // Вызов обработчика маршрута
                if (is_string($route['callback'])) {
                    // Если обработчик задан в формате 'Controller::method'
                    list($controller, $method) = explode('::', $route['callback']);
                    require_once dirname(__DIR__) . "/controllers/{$controller}.php";
                    $controller::$method($data);
                } else {
                    // Если обработчик задан как анонимная функция
                    $route['callback']($data);
                }
                return;
            }
        }
        
        // Если маршрут не найден
        if ($this->notFoundCallback) {
            call_user_func($this->notFoundCallback);
        } else {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Маршрут не найден'
            ]);
        }
    }
} 