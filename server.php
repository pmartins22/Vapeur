<?php

require_once 'router.php';

$router = new Router();
$router->add('/home',     'public/home.html');
$router->add('/profile/', 'public/profile.html');
$router->add('/admin', 'public/admin.html');
$router->add('/game/', 'public/game.html');
$router->add('/login', 'public/login.html');
$router->add('/signin', 'public/signin.html');

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$router->resolve($uri);