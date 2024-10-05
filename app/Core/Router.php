<?php

class Router {
    protected $routes = [];

    public function addRoute($url, $controller, $action) {
        $this->routes[$url] = ['controller' => $controller, 'action' => $action];
    }

    public function dispatch($url) {
        // Remove leading slash if present
        $url = ltrim($url, '/');
        
        // If URL is empty, treat it as the root
        if ($url === '') {
            $url = '/';
        }

        if (array_key_exists($url, $this->routes)) {
            $controller = $this->routes[$url]['controller'];
            $action = $this->routes[$url]['action'];
            
            $controller = new $controller();
            $controller->$action();
        } else {
            throw new Exception("No route found for URL: $url");
        }
    }
}