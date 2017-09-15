<?php
use GuzzleHttp\Psr7\Stream;

final class CommonUtility {

    public static function getBaseUrl($path = '', $encode = FALSE) {

        $host = HOST_NAME;
        $url = "https://{$host}{$path}";

        if($encode) {
            return urlencode($url);
        }
        return $url;
    }

    public static function createStream($resource) {

        return new Stream($resource);
    }

    public static function getServerVar($key = NULL, $default = NULL) {

        if($key === NULL) {
            return $_SERVER;
        }
        if(isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        return $default;
    }

    public static function setVar($key, $val) {

        // Apache environment variable exists, overwrite it
        if (function_exists('apache_getenv') && function_exists('apache_setenv') && apache_getenv($key)) {
            apache_setenv($key, $val);
        }

        if (function_exists('putenv')) {
            putenv("$key=$val");
        }

        $_ENV[$key] = $val;
    }
}