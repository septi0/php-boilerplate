<?php

#[AllowDynamicProperties]
class App
{
    private $app_path;
    private $config;
    private $app_started = false;

    public $di;
    public $router;
    public $template;
    public $session;

    // shortcut to access user data
    public $user_id;
    public $user_role;

    public function __construct($app_path, $di)
    {
        $this->app_path = $app_path;
        $this->config = require $app_path . '/config.php';

        $router = new Router($app_path . '/controllers/', $app_path . '/middlewares/', $this->getConfig('route_qs', []));

        require $app_path . '/routes.php';

        $this->di = $di;
        $this->router = $router;
        $this->template = new Template($app_path . '/views/');
        $this->session = new Session($this->getConfig('sess_name', 'SESSID'));

        $autoload = require $app_path . '/autoload.php';
        $this->autoload($autoload);
    }

    public function run()
    {
        if ($this->app_started) {
            return;
        }

        $this->app_started = true;

        $this->bindErrorHandlers();

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = new Uri($_SERVER);
        $serverParams = $_SERVER;
        $queryParams = $_GET;
        $cookieParams = $_COOKIE;
        $uploadedFiles = $_FILES;
        $parsedBody = $_POST ?: null;
        $body = fopen('php://input', 'r');
        $protocolVersion = isset($_SERVER['SERVER_PROTOCOL']) ? str_replace('HTTP/', '', $_SERVER['SERVER_PROTOCOL']) : '1.1';
        $attributes = [
            'path_info' => $_SERVER['PATH_INFO'] ?? '/',
        ];
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header_name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$header_name] = $value;
            }
        }

        $request = new ServerRequest(
            $method,
            $uri,
            $headers,
            $body,
            $protocolVersion,
            $serverParams,
            $queryParams,
            $cookieParams,
            $uploadedFiles,
            $parsedBody,
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

    public function setUserData($key, $value)
    {
        $this->session->set($key, $value);
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

    public function autoload($autoload)
    {
        if (isset($autoload['helpers'])) {
            foreach ($autoload['helpers'] as $helper) {
                require_once $this->app_path . '/helpers/' . $helper . '.php';

                $helper_name = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $helper));
                $this->{$helper_name} = new $helper($this);
    }
        }
    }

    private function bindErrorHandlers()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        });
    }
}
