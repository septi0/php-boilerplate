<?php

class ResponseHelper
{
    private $base_url;

    public function __construct($base_url)
    {
        $this->base_url = $base_url;
    }

    public function redirect($response, $url, $status = 302)
    {
        $redirect_location = $this->base_url . $url;
        $redirect_location = preg_replace('/\/+/', '/', $redirect_location);

        return $response->withStatus($status)->withHeader('Location', $redirect_location);
    }

    public function json($response, $data, $status = 200)
    {
        $response->getBody()->write(json_encode($data));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}