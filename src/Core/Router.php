<?php

namespace App\Core;

use App\Auth\JWT;
use App\Middleware\IsGranted;
use ReflectionMethod;

class Router
{
    private array $routes = [];
    private JWT $jwt;
    private Container $container;
    private string $basePath;

    private function addRoute(string $method, string $path, string $handler): void
    {
        $this->routes[$method][$path] = $handler;
    }

    private function checkAuthorization(IsGranted $attribute): void
    {
        $user = getCurrentUser();

        if (!$user) {
            http_response_code(401);
            header("Location: " . $this->url('/login'));
            exit;
        }

        if ($attribute->role && (!isset($user->role) || $user->role !== $attribute->role)) {
            http_response_code(403);
            require __DIR__ . '/../../views/error403.php';
            exit;
        }
    }

    private function executeRoute(string $handler, array $params): void
    {
        [$controllerClass, $methodName] = explode("@", $handler);
        $controllerClass = "App\\Controllers\\{$controllerClass}";

        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller {$controllerClass} introuvable");
        }

        $controller = $this->container->resolve($controllerClass);

        $reflection = new ReflectionMethod($controller, $methodName);
        $attributes = $reflection->getAttributes(IsGranted::class);

        if (!empty($attributes)) {
            $attribute = $attributes[0]->newInstance();
            $this->checkAuthorization($attribute);
        }

        call_user_func_array([$controller, $methodName], $params);
    }

    public function __construct(JWT $jwt, Container $container)
    {
        $this->jwt = $jwt;
        $this->container = $container;
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $this->basePath = strtolower(str_replace('/index.php', '', $scriptName));
    }

    public function url(string $path): string
    {
        return $this->basePath . $path;
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
        $uri = strtolower($uri);

        if (strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }

        if (empty($uri)) {
            $uri = '/';
        }

        $_SERVER['BASE_PATH'] = $this->basePath;

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
