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
        $context = [];

        $html = $this->app->template->renderPage('home', $context);
        return ResponseHelper::html($response, $html);
    }
}
