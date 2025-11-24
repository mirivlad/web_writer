<?php
// index.php - единая точка входа
require_once 'config/config.php';

// Простой роутер
class Router {
    private $routes = [];
    
    public function add($pattern, $handler) {
        $this->routes[$pattern] = $handler;
    }
    
    public function handle($uri) {
        // Убираем базовый URL если есть
        $basePath = parse_url(SITE_URL, PHP_URL_PATH) ?? '';
        $uri = str_replace($basePath, '', $uri);
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';
        
        foreach ($this->routes as $pattern => $handler) {
            if ($this->match($pattern, $uri)) {
                return $this->callHandler($handler, $this->params);
            }
        }
        
        // 404
        http_response_code(404);
        include 'views/errors/404.php';
        exit;
    }
    
    private function match($pattern, $uri) {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
        $pattern = "#^$pattern$#";
        
        if (preg_match($pattern, $uri, $matches)) {
            $this->params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return true;
        }
        return false;
    }
    
    private function callHandler($handler, $params) {
        if (is_callable($handler)) {
            return call_user_func_array($handler, $params);
        }
        
        if (is_string($handler)) {
            list($controller, $method) = explode('@', $handler);
            $controllerFile = "controllers/{$controller}.php";
            
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                $controllerInstance = new $controller();
                
                if (method_exists($controllerInstance, $method)) {
                    return call_user_func_array([$controllerInstance, $method], $params);
                }
            }
        }
        
        throw new Exception("Handler not found");
    }
}

// Инициализация роутера
$router = new Router();

// Маршруты
$router->add('/', 'DashboardController@index');
$router->add('/login', 'AuthController@login');
$router->add('/logout', 'AuthController@logout');
$router->add('/register', 'AuthController@register');

// Книги
$router->add('/books', 'BookController@index');
$router->add('/books/create', 'BookController@create');
$router->add('/books/{id}/edit', 'BookController@edit');
$router->add('/books/{id}/delete', 'BookController@delete');
$router->add('/books/{id}/normalize', 'BookController@normalizeContent');
$router->add('/books/{id}/regenerate-token', 'BookController@regenerateToken');

// Главы
$router->add('/books/{book_id}/chapters', 'ChapterController@index');
$router->add('/books/{book_id}/chapters/create', 'ChapterController@create');
$router->add('/chapters/{id}/edit', 'ChapterController@edit');
$router->add('/chapters/{id}/delete', 'ChapterController@delete');
$router->add('/chapters/preview', 'ChapterController@preview');

// Серии
$router->add('/series', 'SeriesController@index');
$router->add('/series/create', 'SeriesController@create');
$router->add('/series/{id}/edit', 'SeriesController@edit');
$router->add('/series/{id}/delete', 'SeriesController@delete');


// Профиль
$router->add('/profile', 'UserController@profile');
$router->add('/profile/update', 'UserController@updateProfile');

// Экспорт с параметром формата
$router->add('/export/{book_id}/{format}', 'ExportController@export');
$router->add('/export/{book_id}', 'ExportController@export'); // по умолчанию pdf
$router->add('/export/shared/{share_token}/{format}', 'ExportController@exportShared');
$router->add('/export/shared/{share_token}', 'ExportController@exportShared'); // по умолчанию pdf

// Публичные страницы
$router->add('/book/{share_token}', 'BookController@viewPublic');
$router->add('/author/{id}', 'UserController@viewPublic');
$router->add('/series/{id}/view', 'SeriesController@viewPublic');

// Обработка запроса
$requestUri = $_SERVER['REQUEST_URI'];
$router->handle($requestUri);

// Редирект с корня на dashboard для авторизованных
$router->add('/', function() {
    if (is_logged_in()) {
        header("Location: " . SITE_URL . "/dashboard");
    } else {
        header("Location: " . SITE_URL . "/login");
    }
    exit;
});


?>