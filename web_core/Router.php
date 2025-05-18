<?php

namespace WebCore;

class NotFoundException extends \Exception
{
    public function __construct($message = '', $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

class Router
{
    private $route_qs;

    private $routes;
    private $routes_map;

    private $middlewares = [];

    private $exception_handlers = [];

    public function __construct($route_qs = '')
    {
        $this->route_qs = $route_qs;
    }

    public function middleware($middleware)
    {
        $this->middlewares[] = $middleware;
    }

    public function register($name, $match, $methods, $handler)
    {
        if (is_string($match)) $match = [$match];
        if (is_string($methods)) $methods = [$methods];

        if (isset($this->routes[$name])) {
            throw new WebCoreException('Route already exists');
    }

        if (count($match) > 2) {
            throw new WebCoreException('Invalid match');
            }

        $route = new Route($name, $match[0], $handler);

        $this->routes[$name] = $route;

        foreach($methods as $method) {
            $method = strtoupper($method);
            $this->routes_map['path'][$match[0]][$method] = $route;
            if (isset($match[1])) {
            $this->routes_map['query'][$match[1]][$method] = $route;
        }
    }

        return $route;
    }

    public function exceptionHandler($exception, $handler)
    {
        $this->exception_handlers[] = [$exception, $handler];
    }

    public function dispatch($request, $app)
    {
        $path = $request->getAttribute('path_info');
        $query_params = $request->getQueryParams();
        $request_method = $request->getMethod();

        if (isset($query_params[$this->route_qs])) {
            // attempt to route based on query param
            $query = $query_params[$this->route_qs];

            if (isset($this->routes_map['query'][$query][$request_method]) && $path == '/') {
                $route = $this->routes_map['query'][$query][$request_method];
            } else {
                $route = new Route('not-found', $path, [$this, 'notFoundCtrl']);
                $route->acl('*');
            }
        } else {
            // attempt to route based on path
            if (isset($this->routes_map['path'][$path][$request_method])) {
                $route = $this->routes_map['path'][$path][$request_method];
            } else {
                $route = new Route('not-found', $path, [$this, 'notFoundCtrl']);
                $route->acl('*');
            }
        }

        $request = $request->withAttribute('route', $route);

        return $this->dispatchRoute($route, $app, $request);
    }

    public function getRoute($name)
    {
        return $this->routes[$name];
    }

    public function routeAllowed($name, $role)
    {
        if (!isset($this->routes[$name])) {
            throw new WebCoreException('Route not found');
        }

        return $this->routes[$name]->allowed($role);
    }

    public function notFoundCtrl($request, $response)
    {
        throw new NotFoundException('Route not found', 404);
    }

    private function dispatchRoute($route, $app, $request)
    {
        $middlewares = [];

        // add app specific middlewares
        $middlewares = array_merge($middlewares, $this->middlewares);
        // add route specific middlewares 
        $middlewares = array_merge($middlewares, $route->middlewares);
        // add controller middleware
        $middlewares[] = $this->genCoreMiddleware($route, $app);

        try {
            return $this->runMiddlewares($middlewares, $app, $request);
        } catch (\Throwable $e) {
            // check if exception handler exists
            foreach ($this->exception_handlers as $handler) {
                $exception = $handler[0];
                $callable = $handler[1];

                if ($e instanceof $exception) {
                    $response = new Response();
                    return CallableInvoker::call($callable, 'index', [$app], [$e, $request, $response]);
                }
            }

            // no handler found, rethrow exception
            throw $e;
        }
    }

    private function genCoreMiddleware($route, $app)
    {
        return new class($route, $app) {
            private $route;
            private $app;

            public function __construct($route, $app)
            {
                $this->route = $route;
                $this->app = $app;
            }

            public function handle($request)
            {
                $response = new Response();
                return CallableInvoker::call($this->route->handler, 'index', [$this->app], [$request, $response]);
            }
        };
    }

    private function runMiddlewares($middlewares, $app, $request)
    {
        $middlewares = array_reverse($middlewares);

        $last = null;

        // create middleware chain
        foreach ($middlewares as $middleware) {
            $next = $last;

            $last = new class($app, $middleware, $next) {
                private $app;
                private $middleware;
                private $next;

                public function __construct($app, $middleware, $next)
                {
                    $this->app = $app;
                    $this->middleware = $middleware;
                    $this->next = $next;
                }

                public function handle($request)
                {
                    return CallableInvoker::call($this->middleware, 'handle', [$this->app], [$request, $this->next]);
                }
            };
        }

        return $last->handle($request);
    }
}
