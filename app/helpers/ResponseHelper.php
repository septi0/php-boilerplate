<?php

use WebCore\Response;

class ResponseHelper
{
    public static function redirect($app, $response, $location, $status = 302)
    {
        if (!$location) {
            throw new Exception('Redirect location not provided');
        }

        if (!$response) {
            $response = new Response();
        }

        // location can be an absolute path, a full URL, or a route name
        if (strpos($location, 'http') === 0) {
            $url = $location;
        } elseif (strpos($location, '/') === 0) {
            $url = $app->baseUrl() . $location;
        } else {
            throw new Exception('Invalid redirect location');
        }

        $redirect_location = $url;
        $redirect_location = preg_replace('/\/+/', '/', $redirect_location);

        return $response->withStatus($status)->withHeader('Location', $redirect_location);
    }

    public static function json($response, $data, $status = 200)
    {
        if (!$response) {
            $response = new Response();
        }

        return $response->withBody(json_encode($data))->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    public static function html($response, $data, $status = 200)
    {
        if (!$response) {
            $response = new Response();
        }

        return $response->withBody($data)->withHeader('Content-Type', 'text/html')->withStatus($status);
    }

    public static function attachment($response, $data, $filename, $content_type = 'application/octet-stream', $status = 200)
    {
        return $response->withBody($data)->withHeader('Content-Type', $content_type)->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')->withStatus($status);
    }
}
