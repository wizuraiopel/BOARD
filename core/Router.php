<?php
// core/Router.php
require_once __DIR__ . '/Security.php';

class Router {

    private $routes = [];

    public function addRoute($method, $action, $handler) {
        $this->routes[strtoupper($method)][$action] = $handler;
    }

public function dispatch($action, $method = 'GET') {
    $method = strtoupper($method);
    
    if (isset($this->routes[$method][$action])) {
        $handler = $this->routes[$method][$action];

        if (is_string($handler)) {
            [$controllerName, $methodName] = explode('@', $handler, 2);
            $controllerClass = $controllerName;
            
            // Try to find the controller file in multiple directories
            $controllerFile = null;
            
            // First check in modules/Inventra/controllers
            $moduleControllerPath = __DIR__ . '/../modules/Inventra/controllers/' . $controllerName . '.php';
            if (file_exists($moduleControllerPath)) {
                $controllerFile = $moduleControllerPath;
            } 
            // Then check in modules/controllers
            elseif (file_exists(__DIR__ . '/../modules/controllers/' . $controllerName . '.php')) {
                $controllerFile = __DIR__ . '/../modules/controllers/' . $controllerName . '.php';
            }
            // Then check in root controllers
            elseif (file_exists(__DIR__ . '/../controllers/' . $controllerName . '.php')) {
                $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';
            }

            if ($controllerFile && file_exists($controllerFile)) {
                require_once $controllerFile;
                if (class_exists($controllerClass) && method_exists($controllerClass, $methodName)) {
                    $controllerInstance = new $controllerClass();
                    call_user_func([$controllerInstance, $methodName]);
                    return;
                } else {
                    $this->handleError(404, "Controller method $controllerClass::$methodName does not exist.");
                }
            } else {
                $this->handleError(404, "Controller file $controllerClass not found.");
            }
        } elseif (is_callable($handler)) {
             call_user_func($handler);
             return;
        } else {
            $this->handleError(500, "Invalid route handler type for: $action");
        }
    }
    
    $this->handleError(404, "Action not found: $action (Method: $method)");
}



    private function handleError($code, $message) {
        http_response_code($code);
        echo "<h1>Error $code</h1><p>$message</p>";
        exit();
    }
}