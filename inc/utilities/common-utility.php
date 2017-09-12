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
}