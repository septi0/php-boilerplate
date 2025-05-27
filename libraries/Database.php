<?php

class Database extends PDO
{
    private $transactions = 0;

    public function __construct($host, $port, $dbname, $user, $pass, $options = [])
    {
        $dsn = "mysql:dbname={$dbname};port={$port};host={$host}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => true, // Not ideal, but needed for backwards compatibility with the old code
        ];

        $init_commands = [
            "SET sql_mode=''", // Disable strict mode
        ];

        if (isset($options['tz']) && $options['tz']) {
            $init_commands[] = 'SET time_zone = "' . $options['tz'] . '"';
        }

        if ($init_commands) {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = implode(';', $init_commands);
        }

        parent::__construct($dsn, $user, $pass, $options);
    }

    public function beginTransaction(): bool
    {
        $ret = true;

        if ($this->transactions == 0) {
            $ret = parent::beginTransaction();
        }

        $this->transactions++;

        return $ret;
    }

    public function commit(): bool
    {
        $ret = true;

        if ($this->transactions == 1) {
            $ret = parent::commit();
        }

        $this->transactions--;

        return $ret;
    }

    public function rollback(): bool
    {
        $ret = true;

        if ($this->transactions == 1) {
            $ret = parent::rollback();
        }

        $this->transactions--;

        return $ret;
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
        $memoized_def = [];
        $operators = [
            'EQ' => '=',
            'LT' => '<',
            'GT' => '>',
            'LTE' => '<=',
            'GTE' => '>=',
            'NE' => '!=',
        ];

        foreach ($filters_definitions as $index => $def) {
            if (!is_array($def)) {
                $def = ['key' => $def];
            }

            if (!isset($def['key'])) {
                throw new Exception("Invalid filter definition");
            }

            if (isset($memoized_def[$def['key']])) {
                throw new Exception("Duplicate filter key {$def['key']}");
            }

            $memoized_def[$def['key']] = $index;
            $filters_definitions[$index] = $def;
        }

        foreach ($fields as $key => $values) {
            $operator = 'EQ';

            if (strpos($key, ':') !== false) {
                $key_parts = explode(':', $key, 2);
                $key = $key_parts[0];
                $operator = strtoupper($key_parts[1]);
            }

            if (!isset($operators[$operator])) {
                throw new Exception("Invalid operator $operator for filter $key");
            }

            if (!isset($memoized_def[$key])) {
                throw new Exception("Filter definition for filter $key not found");
            }

            $filter_definition = $filters_definitions[$memoized_def[$key]];
            $field = $filter_definition['field'] ?? $key;

            if (!is_array($values)) $values = [$values];

            $or_conditions = [];

            foreach ($values as $index => $value) {

                if (isset($filter_definition['expression'])) $expr = '(' . $filter_definition['expression'] . ')';
                else $expr = $field;

                    if ($value === null) {
                    if ($operator == 'EQ') $or_conditions[] = "{$expr} IS NULL";
                    elseif ($operator == 'NE') $or_conditions[] = "{$expr} IS NOT NULL";
                    else throw new Exception("Invalid operator $operator for filter $key with NULL value");
                    } else {
                    $id = md5($key.$operator.$index.microtime(true));
                    $or_conditions[] = "{$expr} {$operators[$operator]} :{$id}";
                    $data["{$id}"] = $value;
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
