<?php

class ResponseHelper
{
    private $base_url;

    public function __construct($base_url)
    {
        $this->base_url = $base_url;
    }

    public function redirect($response, $location, $status = 302)
    {
        if (!$location) {
            throw new Exception('Redirect location not provided');
        }

        // location can be an absolute path, a full URL, or a route name
        if (strpos($location, 'http') === 0) {
            $url = $location;
        } elseif (strpos($location, '/') === 0) {
            $url = $this->base_url . $location;
        } else {
            throw new Exception('Invalid redirect location');
        }

        $redirect_location = $url;
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
