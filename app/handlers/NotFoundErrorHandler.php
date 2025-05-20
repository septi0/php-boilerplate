<?php

class NotFoundErrorHandler
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function index($ex, $request, $response)
    {
        if ($request->getHeader('Accept') == 'application/json') {
            return ResponseHelper::json($response, ['error' => 'The requested resource was not found'], 404);
        } else {
            $html = $this->app->template->renderPage('not_found');
            return ResponseHelper::html($response, $html, 404);
        }
    }
}
