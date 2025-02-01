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
        return $response->withBody(json_encode($data))->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    public function html($response, $data, $status = 200)
    {
        if (!$response) {
            $response = new Response();
        }

        return $response->withBody($data)->withHeader('Content-Type', 'text/html')->withStatus($status);
    }
}
