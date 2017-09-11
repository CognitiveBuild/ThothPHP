<?php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Endroid\QrCode\QrCode;

final class DistributionManager {

    public static function getDistributions() {

        return db::query("SELECT * FROM `build`;");
    }

    public static function getDistributionById($id) {

        $build = new BuildModel();

        if($id > BuildModel::NEW_ID) {

            $result = db::queryFirst("SELECT * FROM `build` WHERE `id` = ?;", array($id));
            if($result != NULL) {
                $build->setId($result['id']);
                $build->setUid($result['uid']);
                $build->setName($result['name']);
                $build->setDisplay($result['display']);
                $build->setPlatform($result['platform']);
                $build->setRegion($result['region']);
                $build->setContainer($result['container']);
                $build->setVersion($result['version']);
                $build->setTime($result['time']);
            }
        }

        return $build;
    }

    public static function getQRCodeById($id) {

        $url = CommonUtility::getBaseUrl("/download/{$id}");
        $qrCode = new QrCode($url);

        return $qrCode;
    }

    public static function getMetadataLink($id) {

        return CommonUtility::getBaseUrl("/api/v1/download/meta/{$id}", TRUE);
    }

    public static function getDownloadUrl($id, $platform = BuildModel::IOS) {

        if($platform === BuildModel::IOS) {
            $metaLink = CommonUtility::getBaseUrl("/api/v1/download/meta/{$id}", TRUE);
            return "itms-services://?action=download-manifest&amp;url={$metaLink}";
        }

        return CommonUtility::getBaseUrl("/api/v1/download/{$id}", TRUE);
    }

    public static function addFiles($build, $files) {

        foreach($files as $file) {

            $size = $file->getSize();
            if($size == 0) continue;

            $binary = file_get_contents($file->file);
            $name = $file->getClientFilename();
            $type = $file->getClientMediaType();

            $result = DistributionManager::sendBuild('PUT', $build->getUid(), $build->getVersion(), $build->getPlatform(), $binary);

            self::addDistribution($build);
        }
    }

    public static function addDistribution($build) {
        return db::insert("INSERT INTO `build` (`name`, `display`, `uid`, `platform`, `region`, `container`, `version`) VALUES (?,?,?,?,?,?,?);", 
            array($build->getName(), $build->getDisplay(), $build->getUid(), $build->getPlatform(), $build->getRegion(), $build->getContainer(), $build->getVersion())
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