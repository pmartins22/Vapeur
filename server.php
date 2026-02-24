<?php

$router = new Router();
$router->add('/home',     'public/home.html');
$router->add('/contato',  'public/contato.html');
$router->add('/profile/', 'public/profile.html');
$router->add('/produto/', 'public/produto.html');
$router->add('/register',  'public/register.php');
$router->add('/login',     'public/login.php');


$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$router->resolver($uri);
?>