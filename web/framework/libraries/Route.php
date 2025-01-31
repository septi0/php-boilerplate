<?php

class Route
{
    private $name;
    private $type;
    private $match;
    private $method;
    private $controller;
    private $action;
    private $middlewares = [];

    public function __construct($name, $type, $match, $method, $controller, $action, $middlewares = [])
    {
        $this->name = $name;
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
