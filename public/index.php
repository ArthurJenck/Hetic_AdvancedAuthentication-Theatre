<?php

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

require __DIR__ . '/../src/helpers.php';
require __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Auth\JWT;

$jwt = JWT::getInstance();
$router = new Router($jwt);

$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/verify-2fa', 'AuthController@showVerify2FA');
$router->post('/verify-2fa', 'AuthController@verify2FA');
$router->get('/logout', 'AuthController@logout');

$router->get('/spectacles/create', 'SpectacleController@showCreate');
$router->post('/spectacles/create', 'SpectacleController@create');

$router->get('/spectacles', 'SpectacleController@list');
$router->get("/spectacles/{id}", 'SpectacleController@show');

$router->post('/reservations', 'ReservationController@create');
$router->get('/profile', 'ReservationController@myReservations');
$router->post('/refresh', 'AuthController@refresh');

$router->run();
