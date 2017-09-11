<?php
final class CommonUtility {

    public static function getBaseUrl($path = '', $encode = FALSE) {
        $host = HOST_NAME;
        $url = "https://{$host}{$path}";

        if($encode) {
            return urlencode($url);
        }
        return $url;
    }
}