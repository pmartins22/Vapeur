<?php

require_once __DIR__ . '/src/router.php';

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$staticFile = __DIR__ . '/public' . $uri;

if (file_exists($staticFile) && !is_dir($staticFile)) {
    $ext = pathinfo($staticFile, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
    ];
    $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
    header("Content-Type: $mime");
    readfile($staticFile);
    exit;
}

$router = new Router();
$router->add('/',          'public/index.php');
$router->add('/home',      'public/home.php');
$router->add('/profile/',  'public/profile.php');
$router->add('/admin',     'public/admin.php');
$router->add('/game/',     'public/game.php');
$router->add('/register',  'public/register.php');
$router->add('/login',     'public/login.php');

#$router->printRoutes();
$router->resolve($uri);