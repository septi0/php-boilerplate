<?php

class App
{
    public $router;
    public $template;
    public $session;

    public function __construct($app_path)
    {
        $router = new Router($app_path . '/controllers/', 'p');

        require_once $app_path . '/routes.php';

        $this->router = $router;
        $this->template = new Template($app_path . '/views/');
        $this->session = new Session('APP_SESS');
    }

    public function run()
    {
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
}
