<?php

class Route
{
    private $type;
    private $match;
    private $method;
    private $controller;
    private $action;
    private $middlewares = [];

    public function __construct($type, $match, $method, $controller, $action, $middlewares = [])
    {
        $this->type = $type;
        $this->match = $match;
        $this->method = $method;
        $this->controller = $controller;
        $this->action = $action;
        $this->middlewares = $middlewares;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        throw new Exception('Invalid property ' . $property);
    }
}
