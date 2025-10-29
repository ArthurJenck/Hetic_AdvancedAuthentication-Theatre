<?php

namespace App\Core;

use App\Auth\JWT;
use App\Middleware\IsGranted;
use ReflectionMethod;

class Router
{
    private array $routes = [];
    private JWT $jwt;

    private function addRoute(string $method, string $path, string $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    private function checkAuthorization(IsGranted $attribute): void
    {
        $token = $_COOKIE['token'] ?? null;

        if (!$token) {
            http_response_code(401);
            header("Location: /login");
            exit;
        }

        $payload = $this->jwt->validateJWT($token);

        if (!$payload) {
            http_response_code(401);
            setcookie('token', '', time() - 3600, "/");
            header('Location: /login');
            exit;
        }

        if ($attribute->role && (!isset($payload->role) || $payload->role !== $attribute->role)) {
            http_response_code(403);
            require __DIR__ . '/../../views/error403.php';
            exit;
        }

        $_SESSION['user'] = $payload;
    }

    private function executeRoute(string $handler, array $params): void
    {
        [$controllerClass, $methodName] = explode("@", $handler);
        $controllerClass = "App\\Controllers\\{$controllerClass}";

        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller {$controllerClass} introuvable");
        }

        $controller = new $controllerClass();

        $reflection = new ReflectionMethod($controller, $methodName);
        $attributes = $reflection->getAttributes(IsGranted::class);

        if (!empty($attributes)) {
            $attribute = $attributes[0]->newInstance();
            $this->checkAuthorization($attribute);
        }

        call_user_func_array([$controller, $methodName], $params);
    }

    public function __construct(JWT $jwt)
    {
        $this->jwt = $jwt;
    }

    public function get(string $path, string $handler): void
    {
        $this->addRoute("GET", $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->addRoute("POST", $path, $handler);
    }

    public function run(): void
    {
        $method = $_SERVER["REQUEST_METHOD"];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $uri = preg_replace('#^/AuthentificationAvancee/theatre/public#', '', $uri);

        if (isset($this->routes[$method][$uri])) {
            $this->executeRoute($this->routes[$method][$uri], []);
            return;
        }

        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
            $pattern = "#^{$pattern}$#";

            if (preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->executeRoute($handler, $params);
                return;
            }
        }

        http_response_code(404);
        echo "404 - Page non trouvée";
    }
}
