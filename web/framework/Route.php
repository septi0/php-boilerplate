<?php

class Route
{
    private $name;
    private $path;
    private $controller;
    private $action;
    private $middlewares = [];
    private $acl = [];

    public function __construct($name, $path, $controller, $action)
    {
        $this->name = $name;
        $this->path = $path;
        $this->controller = $controller;
        $this->action = $action;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        throw new Exception('Invalid property ' . $property);
    }

    public function middleware($middlewares)
    {
        if (is_string($middlewares)) $middlewares = [$middlewares];

        $this->middlewares = array_merge($this->middlewares, $middlewares);
        return $this;
    }

    public function acl($roles)
    {
        if (is_string($roles)) $roles = [$roles];

        $this->acl = array_merge($this->acl, $roles);
        return $this;
    }

    public function allowed($role)
    {
        return in_array('*', $this->acl) || in_array($role, $this->acl);
    }
}
