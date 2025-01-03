<?php

/**
* Router class handles URL routing and controller dispatch
* Implements a simple routing system for mapping URLs to controller actions
* 
* Features:
* - URL to controller/action mapping
* - Dynamic controller instantiation
* - Automatic namespace handling
* - Route validation
*/
class Router {
   /**
    * @var array Stores registered routes and their controller mappings
    */
   protected $routes = [];

   /**
    * Registers a new route with its controller and action
    * 
    * @param string $url The URL path to match
    * @param string $controller The controller class name (without namespace)
    * @param string $action The controller method to call
    * @return void
    * 
    * Example usage:
    * $router->addRoute('/', 'HomeController', 'index');
    * $router->addRoute('admin', 'AdminController', 'dashboard');
    */
   public function addRoute($url, $controller, $action) {
       $this->routes[$url] = [
           'controller' => $controller,
           'action' => $action
       ];
   }

   /**
    * Dispatches the request to the appropriate controller and action
    * 
    * Process:
    * 1. Normalizes the URL by removing leading slash
    * 2. Maps empty URL to root route ('/')
    * 3. Looks up the route configuration
    * 4. Instantiates the controller with namespace
    * 5. Calls the specified action
    * 
    * Security considerations:
    * - Validates route existence before dispatch
    * - Uses proper namespace for controllers
    * - Throws exception for invalid routes
    * 
    * @param string $url The URL path to dispatch
    * @throws \Exception When no matching route is found
    * @return void
    */
    public function dispatch($url) {
        // Remove base path from URL
        $basePath = parse_url(BASE_PATH, PHP_URL_PATH);
        $url = str_replace($basePath, '', $url);
        
        // Remove any public/ references
        $url = str_replace('public/', '', $url);
        
        // Remove leading and trailing slashes
        $url = trim($url, '/');
        
        // If URL is empty, treat as root
        if ($url === '') {
            $url = '/';
        }

        // Check if route exists
        if (array_key_exists($url, $this->routes)) {
            $controllerName = $this->routes[$url]['controller'];
            $action = $this->routes[$url]['action'];
            
            // Add namespace to controller name
            $controllerName = "App\\Controllers\\" . $controllerName;
            
            // Instantiate controller and call action
            $controller = new $controllerName();
            $controller->$action();
        } else {
            throw new \Exception("No route found for URL: $url");
        }
    }

   /**
    * Gets all registered routes
    * Useful for debugging and route listing
    * 
    * @return array Array of registered routes with their controllers and actions
    */
   public function getRoutes() {
       return $this->routes;
   }

   /**
    * Checks if a route exists
    * 
    * @param string $url The URL to check
    * @return bool True if route exists, false otherwise
    */
   public function routeExists($url) {
       return array_key_exists(ltrim($url, '/'), $this->routes);
   }
}