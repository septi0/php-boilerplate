<?php

class ValidationHelper
{
    private static $empty_values = ['', null, false];

    public static function allowed($data, $fields)
    {
        foreach ($data as $key => $value) {
            if (!in_array($key, $fields)) {
                return false;
            }
        }

        return true;
    }

    public static function required($data, $fields)
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || in_array($data[$field], self::$empty_values, true)) {
                return false;
            }
        }

        return true;
    }

    public static function requiredIfExists($data, $fields)
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && in_array($data[$field], self::$empty_values, true)) {
                return false;
            }
        }

        return true;
    }
}
