<?php

class Router {
    private $routes = [];

    public function add($uri, $arquivo) {
        $this->routes[$uri] = $arquivo;
    }

    public function resolve($uri) {
        foreach ($this->routes as $route => $file) {
            if (str_ends_with($route, '/')) {
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
        include 'public/404.html';
    }
}