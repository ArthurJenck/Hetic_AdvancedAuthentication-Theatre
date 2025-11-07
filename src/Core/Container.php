<?php

namespace App\Core;

use Exception;
use ReflectionClass;
use ReflectionException;

class Container
{
    private static ?self $instance = null;
    private array $services = [];
    private array $singletons = [];

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register(string $abstract, callable $factory, bool $singleton = false): void
    {
        $this->services[$abstract] = [
            'factory' => $factory,
            'singleton' => $singleton,
        ];
    }

    public function singleton(string $abstract, callable $factory): void
    {
        $this->register($abstract, $factory, true);
    }

    public function resolve(string $abstract): mixed
    {
        if (isset($this->singletons[$abstract])) {
            return $this->singletons[$abstract];
        }

        if (isset($this->services[$abstract])) {
            $service = $this->services[$abstract];
            $instance = $service['factory']($this);

            if ($service['singleton']) {
                $this->singletons[$abstract] = $instance;
            }

            return $instance;
        }

        return $this->autoResolve($abstract);
    }

    private function autoResolve(string $className): mixed
    {
        try {
            $reflection = new ReflectionClass($className);

            if (!$reflection->isInstantiable()) {
                throw new Exception("La classe {$className} n'est pas instanciable");
            }

            $constructor = $reflection->getConstructor();

            if ($constructor === null) {
                return new $className();
            }

            $parameters = $constructor->getParameters();
            $dependencies = [];

            foreach ($parameters as $parameter) {
                $type = $parameter->getType();

                if ($type === null) {
                    throw new Exception("Impossible de résoudre le paramètre {$parameter->getName()} sans type");
                }

                $typeName = $type->getName();
                $dependencies[] = $this->resolve($typeName);
            }

            return $reflection->newInstanceArgs($dependencies);
        } catch (ReflectionException $e) {
            throw new Exception("Erreur lors de la résolution de {$className}: " . $e->getMessage());
        }
    }

    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}

