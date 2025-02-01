<?php

class Route
{
    private $name;
    private $path;
    private $method;
    private $controller;
    private $action;
    private $middlewares = [];

    public function __construct($name, $path, $method, $controller, $action, $middlewares = [])
    {
        $this->name = $name;
        $this->path = $path;
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
