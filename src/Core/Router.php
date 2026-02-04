<?php

declare(strict_types=1);

namespace Core;

final class Router
{
    /** @var array<int, array{method:string, pattern:string, handler:callable|array, name?:string}> */
    private array $routes = [];

    public function get(string $pattern, callable|array $handler): self
    {
        return $this->map('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable|array $handler): self
    {
        return $this->map('POST', $pattern, $handler);
    }

    public function map(string $method, string $pattern, callable|array $handler): self
    {
        $this->routes[] = ['method' => strtoupper($method), 'pattern' => $pattern, 'handler' => $handler];
        return $this;
    }

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as $r) {
            if ($r['method'] !== $method) continue;
            $params = $this->match($r['pattern'], $path);
            if ($params === null) continue;

            $h = $r['handler'];
            if (is_array($h)) {
                [$class, $action] = $h;
                $controller = new $class();
                $controller->$action(...array_values($params));
                return;
            }

            $h(...array_values($params));
            return;
        }

        http_response_code(404);
        echo View::render('errors/404', ['path' => $path]);
    }

    /** @return array<string,string>|null */
    private function match(string $pattern, string $path): ?array
    {
        // pattern: /admin/locais/{id}/edit
        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';
        if (!preg_match($regex, $path, $m)) return null;

        $params = [];
        foreach ($m as $k => $v) {
            if (is_string($k)) $params[$k] = (string)$v;
        }
        return $params;
    }
}

