<?php

class Di
{
    private $definitions = [];

    public function set($name, $value, $singleton = false)
    {
        $is_callable = is_callable($value);

        $this->definitions[$name] = [
            'value' => $value,
            'callable' => $is_callable,
            'singleton' => $is_callable && $singleton,
            'instance_cache' => null,
        ];
    }

    public function get($name)
    {
        if (!isset($this->definitions[$name])) {
            throw new Exception('No definition found for ' . $name);
        }

        if (!$this->definitions[$name]['callable']) {
            return $this->definitions[$name]['value'];
        }

        if ($this->definitions[$name]['singleton']) {
            if ($this->definitions[$name]['instance_cache'] === null) {
                $this->definitions[$name]['instance_cache'] = $this->definitions[$name]['value']($this);
            }

            return $this->definitions[$name]['instance_cache'];
        } else {
            return $this->definitions[$name]['value']($this);
        }
    }
}