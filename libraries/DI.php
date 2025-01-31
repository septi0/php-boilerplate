<?php

class Di
{
    private $services = [];

    public function __construct($definitions = [])
    {
        if (!empty($definitions)) {
            foreach ($definitions as $name => $service) {
                $this->set($name, $service);
            }
        }
    }

    // Register a service in the container
    public function set($name, $service)
    {
        $this->services[$name] = $service;
    }

    // Retrieve a service from the container
    public function get($name)
    {
        if (!isset($this->services[$name])) {
            throw new Exception("Service {$name} not found.");
        }

        // If the service is a callable (closure), resolve and return it
        if (is_callable($this->services[$name])) {
            return $this->services[$name]($this);
        }

        // Return the service if it's already an object
        return $this->services[$name];
    }
}