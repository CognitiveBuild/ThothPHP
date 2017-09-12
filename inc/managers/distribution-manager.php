<?php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Endroid\QrCode\QrCode;

final class DistributionManager {

    private static $buildSQL = 'SELECT `build`.* FROM `build` WHERE `idbuild` = ?;';

    public static function getApps() {

        return db::query("SELECT * FROM `app` ORDER BY `name` ASC");
    }

    public static function getAppById($id) {

        $app = new AppModel();

        if($id > AppModel::NEW_ID) {
            $result = db::queryFirst("SELECT * FROM `app` WHERE `id` = ?", array($id));
            if($result != NULL) {
                $app->setId($result['id']);
                $app->setName($result['name']);
                $app->setRegion($result['region']);
                $app->setContainer($result['container']);
            }
        }

        return $app;
    }

    public static function getBuildsByAppId($id) {

        return db::query("SELECT * FROM `build` WHERE `idapp` = ? ORDER BY `time` DESC", array($id));
    }

    public static function getBuildById($id) {

        $build = new BuildModel();

        if($id > BuildModel::NEW_ID) {

            $result = db::queryFirst(self::$buildSQL, array($id));
            if($result != NULL) {
                $build->setBuildId($result['idbuild']);
                $build->setAppId($result['idapp']);
                $build->setUid($result['uid']);
                $build->setDisplay($result['display']);
                $build->setPlatform($result['platform']);
                $build->setVersion($result['version']);
                $build->setTime($result['time']);
            }
        }

        return $build;
    }

    public static function getQRCodeById($id) {

        $url = CommonUtility::getBaseUrl("/app/{$id}");
        $qrCode = new QrCode($url);

        return $qrCode;
    }

    public static function getMetadataLink($id) {

        return CommonUtility::getBaseUrl("/api/v1/build/meta/{$id}", TRUE);
    }

    public static function getDownloadUrl($id, $platform = BuildModel::IOS) {

        if($platform === BuildModel::IOS) {
            $metaLink = CommonUtility::getBaseUrl("/api/v1/build/meta/{$id}", TRUE);
            return "itms-services://?action=download-manifest&amp;url={$metaLink}";
        }

        return CommonUtility::getBaseUrl("/api/v1/app/{$id}", TRUE);
    }

    public static function addFiles($app, $build, $files) {

        foreach($files as $file) {

            $size = $file->getSize();
            if($size == 0) continue;

            $binary = file_get_contents($file->file);
            $name = $file->getClientFilename();
            $type = $file->getClientMediaType();

            $result = DistributionManager::sendBuild('PUT', $build->getUid(), $build->getVersion(), $build->getPlatform(), $binary, $app->getRegion(), $app->getContainer());

            self::addBuild($build);
        }
    }

    public static function removeFile($app, $build) {

        try{
            $result = DistributionManager::sendBuild('DELETE', $build->getUid(), $build->getVersion(), $build->getPlatform(), NULL, $app->getRegion(), $app->getContainer());
        }
        catch(Exception $ex){

        }
        self::removeBuild($build);
    }

    public static function addApp($app) {
        return db::insert("INSERT INTO `app` (`name`, `region`, `container`) VALUES (?,?,?);", 
            array($app->getName(), $app->getRegion(), $app->getContainer())
        );
    }

    public static function updateApp($app) {
        return db::execute("UPDATE `app` SET `name` = ?, `region` = ?, `container` = ? WHERE `id` = ?;", 
            array($app->getName(), $app->getRegion(), $app->getContainer(), $app->getId())
        );
    }

    public static function addBuild($build) {
        return db::insert("INSERT INTO `build` (`idapp`, `display`, `uid`, `platform`, `version`) VALUES (?,?,?,?,?);", 
            array($build->getAppId(), $build->getDisplay(), $build->getUid(), $build->getPlatform(), $build->getVersion())
        );
    }

    public static function removeBuild($build) {
        return db::execute("DELETE FROM `build` WHERE `idbuild` = ?", array($build->getBuildId()));
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

    public static function sendBuild($method, $uid, $version, $platform, $file = NULL, $region = 'dallas', $container = 'builds', $useStream = FALSE) {

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

        if($useStream) {
            $context = stream_context_create($payload);
            $fp = fopen($binaryUrl, 'r', FALSE, $context);
            // fpassthru($fp);
            // fclose($fp);
            return $fp;
        }

        return $client->request($method, $binaryUrl, $payload);
    }

}