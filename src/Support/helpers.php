<?php
use \Impress\Support\Str;

if (!function_exists("config")) {
    function config($parameters, $default = null)
    {
        $value = \Impress\Framework\Config\Config::get($parameters);
        if ($value === null) return value($default);
        return $value;
    }
}

if (!function_exists("is_production")) {
    function is_production()
    {
        return boolval((env('ENV', 'production') == "production"));
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) return value($default);

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return null;
        }

        if (Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }
        return $value;
    }
}


if (!function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

function root_path($path = '')
{
    return IMPRESS_PHP_ROOT_PATH . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}

function vendor_path($path = '')
{
    return root_path("vendor") . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}

function config_path($path = '')
{
    return root_path("config") . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}

function public_path($path = '')
{
    return root_path("public") . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}

function resources_path($path = '')
{
    return root_path("resources") . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}

function storage_path($path = '')
{
    return root_path("storage") . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}

function app_path($path = '')
{
    return root_path("app") . ($path ? DIRECTORY_SEPARATOR . $path : $path);
}
