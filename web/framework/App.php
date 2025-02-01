<?php

class App
{
    private $app_path;
    private $router;
    private $config;

    private $app_started = false;

    public $template;
    public $session;
    public $response_helper;
    public $di;

    public $user_id;
    public $user_role;

    public function __construct($app_path, $di)
    {
        $router = new Router($app_path . '/controllers/', 'p');

        require_once $app_path . '/routes.php';

        $this->app_path = $app_path;
        $this->router = $router;
        $this->config = require $app_path . '/config.php';

        $this->template = new Template($app_path . '/views/');
        $this->session = new Session($this->getConfig('sess_name', 'SESSID'));
        $this->response_helper = new ResponseHelper($this->baseUrl());
        $this->di = $di;
    }

    public function run()
    {
        if ($this->app_started) {
            return;
        }

        $this->app_started = true;

        $this->bindErrorHandlers();

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $this->buildUri();
        $headers = $this->getHeaders();
        $body = fopen('php://input', 'r');
        $query_params = $_GET ?? [];
        $cookie_params = $_COOKIE ?? [];
        $server_params = $_SERVER ?? [];
        $files = $_FILES ?? [];
        $parsed_body = $_POST ?? [];
        $attributes = [
            'path_info' => $_SERVER['PATH_INFO'] ?? '/',
        ];

        $request = new Request(
            $method,
            $uri,
            $headers,
            $body,
            $query_params,
            $cookie_params,
            $server_params,
            $files,
            $parsed_body,
            $attributes
        );

        $response = $this->router->dispatch($request, $this);

        (new ResponseEmitter())->emit($response);
    }

    public function getConfig($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function getUserdata($key, $default = null)
    {
        return $this->session->get($key, $default);
    }

    public function setUser($user_id, $role)
    {
        $this->user_id = $user_id;
        $this->user_role = $role;
    }

    public function baseUrl($path = '')
    {
        $base_url = $this->getConfig('base_url', '/');
        return rtrim($base_url, '/') . '/' . ltrim($path, '/');
    }

    private function buildUri()
    {
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $request_uri = $_SERVER['REQUEST_URI'] ?? '/';

        return $scheme . '://' . $host . $request_uri;
    }

    private function getHeaders()
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerName = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$headerName] = [$value];
            }
        }

        return $headers;
    }

    private function bindErrorHandlers()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }
}
