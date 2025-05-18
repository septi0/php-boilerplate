<?php

namespace WebCore;

class CallableInvoker
{
    public static function call($callable, $default_method, $construct_params, $callable_params)
    {
        $method = $default_method;

        if (is_array($callable)) {
            $callable_arr = $callable;

            $callable = $callable_arr[0];
            $method = $callable_arr[1] ?? $default_method;
        }

        if (is_string($callable)) {
            $callable = new $callable(...$construct_params);
        }

        if (is_object($callable)) {
            if (!$method || !method_exists($callable, $method)) {
                var_dump($method, $callable);
                throw new WebCoreException('Method not provided');
            }

            return call_user_func_array([$callable, $method], $callable_params);
        } elseif (is_callable($callable)) {
            return call_user_func_array($callable, $callable_params);
        } else {
            throw new WebCoreException('Invalid callable type');
        }
    }
}