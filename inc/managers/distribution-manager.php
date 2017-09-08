<?php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
final class DistributionManager {

    public static function getDistributions() {

        return db::query("SELECT * FROM `distribution`;");
    }

    public static function getDistributionById($id) {

        return db::queryFirst("SELECT * FROM `distribution` WHERE `id` = ?;", array($id));
    }

    public static function getMetadataLink($id) {

        $host = $_SERVER['HTTP_HOST'];
        if(IS_LOCAL) {
            $host = 'thoth-assets.mybluemix.net';
        }
        return urlencode("https://{$host}/api/v1/download/meta/{$id}");
    }

    public static function addFiles($build, $files) {

        foreach($files as $file) {

            $size = $file->getSize();
            if($size == 0) continue;

            $binary = file_get_contents($file->file);
            $name = $file->getClientFilename();
            $type = $file->getClientMediaType();

            $result = DistributionManager::sendBuild('PUT', $build['uid'], $build['version'], $build['platform'], $binary);

            self::addDistribution($build['name'], $build['display'], $build['uid'], $build['platform'], $build['region'], $build['container'], $build['version']);
        }
    }

    public static function addDistribution($name, $display, $uid, $platform, $region, $container, $version) {
        return db::insert("INSERT INTO `distribution` (`name`, `display`, `uid`, `platform`, `region`, `container`, `version`) VALUES (?,?,?,?,?,?,?);", 
            array($name, $display, $uid, $platform, $region, $container, $version)
        );
    }

    private static function send($region) {

        $client = new Client();
        // Send out HTTP request
        $result = $client->request('POST', 'https://identity.open.softlayer.com/v3/auth/tokens', [
            'json' => [
                'auth' => [
                    'identity' => [
                        'methods' => [ 'password' ], 
                        'password' => [ 
                            'user' => [
                                'id' => $_ENV['OBJECT_STORAGE_USER_ID'], 
                                'password' => $_ENV['OBJECT_STORAGE_PASSWORD']
                            ]
                        ]
                    ], 
                    'scope' => [
                        'project' => [
                            'id' => $_ENV['OBJECT_STORAGE_PROJECT_ID']
                        ]
                    ]
                ]
            ], 
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
        
        $content = $result->getBody()->getContents();
        $headers = $result->getHeader('X-Subject-Token');

        $json = json_decode($content, TRUE);

        $base = '';
        $token = $headers[0];

        $catalogs = $json['token']['catalog'];
        foreach($catalogs as $key => $val) {
            $endpoints = $val['endpoints'];
            if('object-store' === $val['type']) {
                foreach($endpoints as $endpointKey => $endpoint) {
                    if($endpoint['region'] === $region && $endpoint['interface'] == 'public') {
                        $base = $endpoint['url'];
                        break;
                    }
                }
            }
        }
        return array(
            'base' => $base, 
            'token' => $token
        );
    }

    public static function sendBuild($method, $uid, $version, $platform, $file = NULL, $region = 'dallas', $container = 'builds') {

        $client = new Client();

        $send = self::send($region);

        $binaryUrl = "{$send['base']}/{$container}/{$uid}.{$version}.{$platform}";

        $payload = [
            'headers' => [
                'X-Auth-Token' => $send['token']
            ]
        ];

        if($file !== NULL && $method === 'PUT') {
            $payload['body'] = $file;
        }

        $result = $client->request($method, $binaryUrl, $payload);
        return $result;
    }

}