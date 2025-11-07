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
use App\Core\Container;
use App\Auth\JWT;
use App\Repositories\UserRepository;
use App\Repositories\TemporaryCodeRepository;
use App\Services\LogService;
use App\Services\UserService;
use App\Services\Auth\TwoFactorAuthenticationService;

$container = Container::getInstance();

$container->singleton(UserRepository::class, fn($c) => new UserRepository());
$container->singleton(TemporaryCodeRepository::class, fn($c) => new TemporaryCodeRepository());
$container->singleton(LogService::class, fn($c) => new LogService());

$container->singleton(UserService::class, function($c) {
    return new UserService($c->resolve(UserRepository::class));
});

$container->singleton(TwoFactorAuthenticationService::class, function($c) {
    return new TwoFactorAuthenticationService(
        $c->resolve(UserRepository::class),
        $c->resolve(TemporaryCodeRepository::class),
        $c->resolve(LogService::class)
    );
});

$jwt = JWT::getInstance();
$router = new Router($jwt, $container);

$router->get('/', 'HomeController@index');

$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');

$router->get('/verify-2fa', 'AuthController@showVerify2FA');
$router->post('/verify-2fa', 'AuthController@verify2FA');

$router->get('/setup-2fa-choice', 'TwoFactorController@showMethodChoice');
$router->post('/setup-2fa-choice', 'TwoFactorController@processMethodChoice');

$router->get('/setup-2fa/totp', 'TwoFactorController@setupTOTP');
$router->get('/setup-2fa/email', 'TwoFactorController@setupEmail');
$router->get('/setup-2fa/sms', 'TwoFactorController@setupSMS');
$router->post('/setup-2fa/sms/phone', 'TwoFactorController@setupSMSPhone');
$router->get('/setup-2fa/sms/verify', 'TwoFactorController@verifySMSCode');
$router->post('/setup-2fa/complete', 'TwoFactorController@completeSetup');

$router->get('/profile/2fa', 'TwoFactorController@manage');
$router->post('/profile/2fa/enable', 'TwoFactorController@enableFromProfile');
$router->post('/profile/2fa/change-method', 'TwoFactorController@changeMethod');
$router->post('/profile/2fa/disable', 'TwoFactorController@disable');

$router->get('/spectacles', 'SpectacleController@list');
$router->get("/spectacles/{id}", 'SpectacleController@show');
$router->get('/spectacles/create', 'SpectacleController@showCreate');
$router->post('/spectacles/create', 'SpectacleController@create');

$router->post('/reservations', 'ReservationController@create');
$router->get('/profile', 'ReservationController@myReservations');

$router->post('/refresh', 'AuthController@refresh');

$router->run();
