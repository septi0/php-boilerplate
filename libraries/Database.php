<?php

class Database extends PDO
{
    public function __construct($host, $port, $dbname, $user, $pass, $options = [])
    {
        $dsn = "mysql:dbname={$dbname};port={$port};host={$host}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $init_commands = [];

        if (isset($options['tz']) && $options['tz']) {
            $init_commands[] = 'SET time_zone = "' . $options['tz'] . '"';
        }

        if ($init_commands) {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = implode(';', $init_commands);
        }

        parent::__construct($dsn, $user, $pass, $options);
    }

    public function genInsertQuery($fields_allowed, $fields)
    {
        foreach ($fields as $key => $value) {
            if (!in_array($key, $fields_allowed)) {
                throw new Exception("Field $key is not allowed");
            }
        }

        $keys = implode(', ', array_keys($fields));
        $values = ':' . implode('_s, :', array_keys($fields)) . '_s';

        $data = [];

        foreach ($fields as $key => $value) {
            $data["{$key}_s"] = $value;
        }

        return ["($keys) VALUES ($values)", $data];
    }

    public function genUpdateQuery($fields_allowed, $fields)
    {
        foreach ($fields as $key => $value) {
            if (!in_array($key, $fields_allowed)) {
                throw new Exception("Field $key is not allowed");
            }
        }

        $update_list = [];
        $data = [];

        foreach ($fields as $key => $value) {
            $update_list[] = "{$key}=:{$key}_u";
            $data["{$key}_u"] = $value;
        }

        return [implode(', ', $update_list), $data];
    }

    public function genFilterQuery($filters_definitions, $fields)
    {
        $and_conditions = [];
        $data = [];

        foreach ($fields as $key => $values) {
            if (!array_key_exists($key, $filters_definitions)) {
                throw new Exception("Filter definition for $key not found");
            }

            $filter_definition = $filters_definitions[$key];
            if (!is_array($values)) $values = [$values];

            $or_conditions = [];

            foreach ($values as $index => $value) {

                if (!$filter_definition) {

                    if ($value === null) {
                        $or_conditions[] = "{$key} IS NULL";
                    } else {
                        $or_conditions[] = "{$key}=:{$key}_f{$index}";
                        $data["{$key}_f{$index}"] = $value;
                    }
                } elseif (is_string($filter_definition)) {

                    if (strpos($filter_definition, ':value') === false) {
                        throw new Exception("Invalid filter definition for $key");
                    }

                    $or_conditions[] = str_replace(':value', ":{$key}_f{$index}", $filter_definition);
                    $data["{$key}_f{$index}"] = $value;
                } else if (is_array($filter_definition)) {

                    if (!isset($filter_definition[$value])) {
                        throw new Exception("Invalid value $value for filter $key");
                    }

                    if ($filter_definition[$value]) {
                        $or_conditions[] = '(' . $filter_definition[$value] . ')';
                    }
                }
            }

            $and_conditions[] = '(' . implode(' OR ', $or_conditions) . ')';
        }

        return [implode(' AND ', $and_conditions), $data];
    }

    public function genOrderQuery($allowed_orderby, $orderby, $default = '')
    {
        $orderby_criterias = $orderby;

        if (!$orderby_criterias) $orderby_criterias = $default;
        if (!is_array($orderby_criterias)) $orderby_criterias = [$orderby_criterias];

        $orderby_list = [];

        foreach ($orderby_criterias as $orderby_criteria) {
            $orderby_parts = explode(':', $orderby_criteria);

            $field = $orderby_parts[0];
            $direction = isset($orderby_parts[1]) ? $orderby_parts[1] : 'ASC';

            if (!in_array($field, $allowed_orderby)) {
                throw new Exception('Invalid orderby field');
            }

            if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) {
                throw new Exception('Invalid orderby direction');
            }

            $orderby_list[] = "{$field} {$direction}";
        }

        return "ORDER BY " . implode(', ', $orderby_list);
    }
}
