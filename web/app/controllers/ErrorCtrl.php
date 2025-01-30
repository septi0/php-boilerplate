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
            return $response->withBody(['error' => 'The requested resource was not found'])->withStatus(404)->withHeader('Content-Type', 'application/json');
        } else {
            return $response->withBody('404 Not Found')->withStatus(404);
        }
    }
}
