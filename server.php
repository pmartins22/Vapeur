<?php

$router = new Router();
$router->add('/',     'public/index.php');
$router->add('/home',     'public/home.php');
$router->add('/profile/', 'public/profile.php');
$router->add('/admin', 'public/admin.php');
$router->add('/game/', 'public/game.php');
$router->add('/register',  'public/register.php');
$router->add('/login',     'public/login.php');


$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$router->resolve($uri);