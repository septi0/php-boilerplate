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
            $html = $this->app->template->renderPage('not_found');
            return $this->app->response_helper->html($response, $html, 404);
        }
    }
}
