<?php

class IndexCtrl
{
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function index($request, $response)
    {
        return $this->app->template->renderPage($response, 'home');
    }
}
