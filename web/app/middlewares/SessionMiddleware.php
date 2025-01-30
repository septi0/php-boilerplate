<?php

class SessionMiddleware {
    private $app;

    public function __construct($app) {
        $this->app = $app;
    }

    public function handle($request, $handler) {
        $session = $this->app->session;
        $session->start();

        return $handler->handle($request);
    }
}