<?php

$routes = [
    '/' => ['controller' => 'HomeController', 'action' => 'index'],
    '/login' => ['controller' => 'LoginController', 'action' => 'login'],
    '/usuarios' => ['controller' => 'UserController', 'action' => 'list'],
    '/usuarios/cadastro' => ['controller' => 'UserController', 'action' => 'create'],
];

function route($routes)
{
    $uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH);

    if (array_key_exists($path, $routes)) {
        $route = $routes[$path];
        $controller = $route['controller'];
        $action = $route['action'];

        $controllerInstance = new $controller();
        $controllerInstance->$action();
    } else {
        // Redirecione para uma página de login ou exiba uma mensagem de erro
        redirect('/login');
    }
}
