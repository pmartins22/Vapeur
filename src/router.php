<?php

class Router {
    private $routes = [];

    public function add($uri, $file) {
        $this->routes[$uri] = $file;
    }

    public function resolve($uri) {
        foreach ($this->routes as $route => $file) {
            if ($route === '/') {
                if ($uri === '/') {
                    include $file;
                    exit;
                }
            } elseif (str_ends_with($route, '/')) {
                if (str_starts_with($uri, $route)) {
                    include $file;
                    exit;
                }
            } else {
                if ($uri === $route) {
                    include $file;
                    exit;
                }
            }
        }

        http_response_code(404);
        include 'public/404.php';
    }

    public function printRoutes() {
        foreach ($this->routes as $route => $file) {
            error_log("ROTA: " . $route . " => " . $file);
        }
    }
}