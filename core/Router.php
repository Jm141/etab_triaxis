<?php
/**
 * Simple Router Class
 */

class Router {
    private $routes = [];
    private $baseUrl;
    
    public function __construct($baseUrl = '') {
        $this->baseUrl = $baseUrl;
    }
    
    public function get($path, $handler) {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post($path, $handler) {
        $this->addRoute('POST', $path, $handler);
    }
    
    private function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }
    
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = str_replace($this->baseUrl, '', $uri);
        $uri = trim($uri, '/');
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                $pattern = $this->convertToRegex($route['path']);
                
                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches); // Remove full match
                    
                    $handler = $route['handler'];
                    
                    if (is_string($handler) && strpos($handler, '@') !== false) {
                        list($controller, $method) = explode('@', $handler);
                        $controllerClass = $controller . 'Controller';
                        $controllerFile = __DIR__ . '/../controllers/' . $controllerClass . '.php';
                        
                        if (file_exists($controllerFile)) {
                            require_once $controllerFile;
                            if (class_exists($controllerClass)) {
                                $controllerInstance = new $controllerClass();
                                if (method_exists($controllerInstance, $method)) {
                                    call_user_func_array([$controllerInstance, $method], $matches);
                                    return;
                                }
                            }
                        }
                    } elseif (is_callable($handler)) {
                        call_user_func_array($handler, $matches);
                        return;
                    }
                }
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        die("404 - Page not found");
    }
    
    private function convertToRegex($path) {
        $path = preg_replace('/\{(\w+)\}/', '([^/]+)', $path);
        return '#^' . $path . '$#';
    }
}



