<?php

class Router
{
    private $controllers_path;
    private $middlewares_path;
    private $route_qs;

    private $routes;
    private $routes_map;

    private $middlewares = [];

    public function __construct($controllers_path, $middlewares_path, $route_qs = '')
    {
        $this->controllers_path = $controllers_path;
        $this->middlewares_path = $middlewares_path;
        $this->route_qs = $route_qs;
    }

    public function middleware($middleware)
    {
        $this->middlewares[] = $middleware;
    }

    public function register($name, $match, $methods, $controller, $action = 'index')
    {
        if (is_string($match)) $match = [$match];
        if (is_string($methods)) $methods = [$methods];

        if (isset($this->routes[$name])) {
            throw new Exception('Route already exists');
    }

        if (count($match) > 2) {
            throw new Exception('Invalid match');
            }

        $route = new Route($name, $match[0], $controller, $action);

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
                $route = new Route('not-found', $query, $request_method, 'ErrorCtrl', 'notFound');
            }
        } else {
            // attempt to route based on path
            if (isset($this->routes_map['path'][$path][$request_method])) {
                $route = $this->routes_map['path'][$path][$request_method];
            } else {
                $route = new Route('not-found', $path, $request_method, 'ErrorCtrl', 'notFound');
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
            throw new Exception('Route not found');
        }

        return $this->routes[$name]->allowed($role);
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

        $this->loadMiddlewares($middlewares);

        return $this->runMiddlewares($middlewares, $app, $request);
    }

    private function genCoreMiddleware($route, $app)
    {
        return new class($route, $app, $this->controllers_path) {
            private $route;
            private $app;
            private $controllers_path;

            public function __construct($route, $app, $controllers_path)
            {
                $this->route = $route;
                $this->app = $app;
                $this->controllers_path = $controllers_path;
            }

            public function handle($request, $next)
            {
                $controller = $this->route->controller;
                $action = $this->route->action;

                $controller_path = $this->controllers_path . '/' . $controller . '.php';

                if (!file_exists($controller_path)) {
                    throw new Exception("Controller {$controller} not found");
                }

                require_once $controller_path;

                $controller_instance = new $controller($this->app);

                if (!method_exists($controller_instance, $action)) {
                    throw new Exception('Action not found');
                }

                $response = new Response();

                return call_user_func_array([$controller_instance, $action], [$request, $response]);
            }
        };
    }

    private function loadMiddlewares($middlewares) {
        foreach($middlewares as $middleware) {
            if (is_string($middleware)) {
                $middleware_path = $this->middlewares_path . '/' . $middleware . '.php';

                if (!file_exists($middleware_path)) {
                    throw new Exception("Middleware {$middleware} not found");
                }

                require_once $middleware_path;
            }
        }
    }

    private function runMiddlewares($middlewares, $app, $request)
    {
        $middlewares = array_reverse($middlewares);

        $last = null;

        // create middleware chain
        foreach ($middlewares as $middleware) {
            $next = $last;

            // create middleware class instance
            if (is_string($middleware)) $middleware = new $middleware($app);

            $last = new class($middleware, $next) {
                private $middleware;
                private $next;

                public function __construct($middleware, $next)
                {
                    $this->middleware = $middleware;
                    $this->next = $next;
                }

                public function handle($request)
                {
                    if (is_object($this->middleware)) {
                        return $this->middleware->handle($request, $this->next);
                    } elseif (is_callable($this->middleware)) {
                        $callable = $this->middleware;
                        return $callable($request, $this->next);
                    } else {
                        throw new Exception('Invalid middleware type');
                    }
                }
            };
        }

        return $last->handle($request);
    }
}
