<?php

class Router
{
    private $controllers_path;
    private $query_param;

    private $routes_map;

    private $middlewares = [];

    public function __construct($controllers_path, $query_param = '')
    {
        $this->controllers_path = $controllers_path;
        $this->query_param = $query_param;
    }

    public function middleware($middleware)
    {
        $this->middlewares[] = $middleware;
    }

    public function get($name, $match, $controller, $action = 'index', $middlewares = [])
    {
        $this->register($name, $match, 'GET', $controller, $action, $middlewares);
    }

    public function post($name, $match, $controller, $action = 'index', $middlewares = [])
    {
        $this->register($name, $match, 'POST', $controller, $action, $middlewares);
    }

    public function register($name, $match, $method, $controller, $action = 'index', $middlewares = [])
    {
        if (is_array($match)) {
            if (count($match) != 2) {
                throw new Exception('Invalid route');
            }

            $route = new Route($name, $match[0], $method, $controller, $action, $middlewares);

            $this->routes_map['path'][$match[0]][$method] = $route;
            $this->routes_map['query'][$match[1]][$method] = $route;
        } else {
            $this->routes_map['path'][$match][$method] = new Route($name, $match, $method, $controller, $action, $middlewares);
        }
    }

    public function dispatch($request, $app)
    {
        $path = $request->getAttribute('path_info');
        $query_params = $request->getQueryParams();
        $request_method = $request->getMethod();

        if (isset($query_params[$this->query_param])) {
            // attempt to route based on query param
            $query = $query_params[$this->query_param];

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

        $request = $request->withAttribute('route', $route->name)->withAttribute('route_path', $route->path);

        return $this->dispatchRoute($route, $app, $request);
    }

    public function getRoute($name, $type = 'path', $method = 'GET')
    {
        return $this->routes_map[$type][$name][$method] ?? null;
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
