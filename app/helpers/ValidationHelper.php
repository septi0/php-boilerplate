<?php

class ValidationHelper
{
    private $app;
    private $empty_values = ['', null, false];

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function allowed($data, $fields)
    {
        foreach ($data as $key => $value) {
            if (!in_array($key, $fields)) {
                return false;
            }
        }

        return true;
    }

    public function required($data, $fields)
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || in_array($data[$field], $this->empty_values, true)) {
                return false;
            }
        }

        return true;
    }

    public function requiredIfExists($data, $fields)
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && in_array($data[$field], $this->empty_values, true)) {
                return false;
            }
        }

        return true;
    }
}
