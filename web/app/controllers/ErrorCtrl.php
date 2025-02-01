<?php

class ErrorCtrl
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function notFound($request, $response)
    {
        if ($request->getHeader('Accept') == 'application/json') {
            return $this->app->response_helper->json($response, ['error' => 'The requested resource was not found'], 404);
        } else {
            return $this->app->response_helper->html($response, '404 Not Found', 404);
        }
    }
}
