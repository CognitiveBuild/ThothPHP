<?php
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use Endroid\QrCode\QrCode;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
                $build->setNotes($result['notes']);
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

    public static function getDownloadUrl($id) {

        return CommonUtility::getBaseUrl("/api/v1/build/download/{$id}");
    }

    public static function getInstallUrl($id, $platform = BuildModel::IOS) {

        if($platform === BuildModel::IOS) {
            $metaLink = CommonUtility::getBaseUrl("/api/v1/build/meta/{$id}", TRUE);
            return "itms-services://?action=download-manifest&amp;url={$metaLink}";
        }

        return self::getDownloadUrl($id);
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
        return db::insert("INSERT INTO `build` (`idapp`, `display`, `uid`, `platform`, `notes`, `version`) VALUES (?,?,?,?,?,?);", 
            array($build->getAppId(), $build->getDisplay(), $build->getUid(), $build->getPlatform(), $build->getNotes(), $build->getVersion())
        );
    }

    public static function updateBuild($build) {
        return db::execute("UPDATE `build` SET `notes` = ? WHERE `idbuild` = ?", array($build->getNotes(), $build->getBuildId()));
    }

    public static function removeBuild($build) {
        return db::execute("DELETE FROM `build` WHERE `idbuild` = ?", array($build->getBuildId()));
    }

    private static function addDistribution($idapp, $idbuild, $iduser, $message, $list) {
        return db::insert("INSERT INTO `distribution` (`idapp`, `idbuild`, `iduser`, `message`, `list`) VALUES (?,?,?,?,?);", 
        array($idapp, $idbuild, $iduser, $message, $list));
    }

    public static function sendBuildEmail($idapp, $idbuild, $iduser, $emails, $message) {

        $build = self::getBuildById($idbuild);

        // In case any of our lines are larger than 70 characters, we should use wordwrap()
        $comments = str_replace("\n\r", "<br /><br />", $message);
        $comments = str_replace("\r", "<br />", $comments);
        $notes = $build->getNotesHTML();

        $downloadUrl = DistributionManager::getDownloadUrl($build->getBuildId());
        $installUrl = DistributionManager::getInstallUrl($build->getBuildId(), $build->getPlatform());

        $qrCodeUrl = CommonUtility::getBaseUrl("/api/v1/build/code/{$idapp}");

        $body = <<<EOT
<html>
<head>
<title>{$build->getDisplay()}</title>
</head>
<style type="text/css">
    .btn {
        -webkit-appearance: none;
        display: block;
        padding: 6px 12px;
        margin-bottom: 0;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.42857143;
        text-align: center;
        white-space: nowrap;
        vertical-align: middle;
        -ms-touch-action: manipulation;
        touch-action: manipulation;
        cursor: pointer;
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
        background-image: none;
        border: 1px solid transparent;
        border-radius: 4px;
        width: 90%;
    }
    .btn-success {
        color: #fff;
        background-color: #5cb85c;
        border-color: #4cae4c;
        font-weight: bold;
    }
    .btn-info {
        color: #fff;
        background-color: #5bc0de;
        border-color: #46b8da;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .ui-title {
        font-weight: bold;
    }
    .ui-label {
        font-weight: bold;
    }
    .ui-qr-code {
        padding: 10px;
        width: 90%;
    }
    label {
        display: block;
        font-weight: bold;
    }
    a {
        text-decoration: none;
    }
    
</style>
<body>
    <div class="download">

        <div class="form-group">
            <div class="ui-title">{$build->getDisplay()} (v{$build->getVersion()})</div>
            <div class="ui-note"><span class="ui-label">Platform</span>: {$build->getPlatform()}</div>
        </div>

        <div class="form-group">
            <a class="btn btn-success btn-download btn-install" href="{$installUrl}">Install this Build now</a>
        </div>

        <div class="form-group">
            <a class="btn btn-info btn-download" href="{$downloadUrl}">Download from PC</a>
        </div>

        <div class="form-group ui-release-notes">
            <div class="ui-label">Release notes:</div>
            {$build->getNotesHTML()}
        </div>

        <div class="form-group ui-form-group-image-container">
            <div class="ui-label">QR Code:</div>
            <img src="{$qrCodeUrl}" class="ui-qr-code" />
        </div>

        <div class="form-group">
        {$comments}
        </div>

    </div>
</body>
</html>

EOT;

        $subject = "[New Build] {$build->getDisplay()} `v{$build->getVersion()}` is available!";

        // $headers = array();
        // $headers[] = 'MIME-Version: 1.0';
        // $headers[] = 'Content-type: text/html; charset=UTF-8';
        // // Additional headers
        // $headers[] = "To: {$emails}";
        // $headers[] = "From: {$_ENV['SMTP_SENDER_NAME']} <{$_ENV['SMTP_SENDER_EMAIL']}>";

        // $result = mail($emails, $subject, $body, implode("\r\n", $headers));

        $list = array_map('trim', explode(",", $emails));

        $result = self::sendEmail($list, $subject, $body);

        if($result) {
            return self::addDistribution($idapp, $idbuild, $iduser, $comments, $emails);
        }

        return $result;
    }


    public static function sendEmail($emails, $subject, $body) {

        $result = TRUE;
        $mail = new PHPMailer(TRUE);
        $sender = $_ENV['SMTP_SENDER_EMAIL'];
        try {
            $mail->isSMTP();
            // $mail->SMTPDebug = 2;
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = TRUE;
            $mail->Port = $_ENV['SMTP_PORT'];
            $mail->SMTPSecure = $_ENV['SMTP_SECURE']; 
            $mail->Username = $sender;
            $mail->Password = $_ENV['SMTP_SENDER_PASSWORD'];
    
            $mail->setFrom($sender, $_ENV['SMTP_SENDER_NAME']);

            foreach($emails as $email) {

                if(DEBUG) {
                    $mail->addAddress($email);
                }
                else {
                    $mail->addBCC($email);
                }
            }

            $mail->isHTML(true);
    
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();
        }
        catch (Exception $ex) {
            $result = FALSE;
        }
        return $result;
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
            $payload = [
                'http' => [
                    'method' => $method, 
                    'header' => "X-Auth-Token: {$send['token']}"
                ]
            ];

            $context = stream_context_create($payload);
            $fp = fopen($binaryUrl, 'r', FALSE, $context);
            // fpassthru($fp);
            // fclose($fp);
            return $fp;
        }

        return $client->request($method, $binaryUrl, $payload);
    }

}