<?php
include('inc/header.download.html');

$isLatest = FALSE;
$count = count($builds);
foreach($builds as $key => $val) {
    $build = new BuildModel($val['idbuild'], $val['idapp'], $val['uid'], $val['display'], $val['platform'], $val['version'], $val['time']);
    $icon = $build->getPlatform() === BuildModel::IOS ? '<i class="glyphicon glyphicon-apple glyphicon-apple-big"></i>' : '<i class="glyphicon glyphicon-text-color glyphicon-apple-big"></i>';    
    $url = DistributionManager::getDownloadUrl($build->getBuildId());

    if($isLatest) {

        echo <<<EOT
        <li class="list-group-item">
            <a href="{$url}" class="name ui-modal-button">Download v{$build->getVersion()} for {$build->getPlatform()}</a>
        </li>
EOT;
    }
    else {
        $isLatest = TRUE;
    
        echo <<<EOT
        <div id="t-wrapper" class="download">
            <div class="form-group">
                <h1>{$build->getDisplay()} (v{$build->getVersion()})</h1>
                <p class="ui-note">OTA Install for {$build->getPlatform()}</p>
            </div>
        
            <div class="form-group">
                {$icon}
            </div>
        
            <div class="form-group">
                <p class="ui-notice ui-notice-green">{$build->getTime()}</p>
            </div>
        
            <div class="form-group">
                <a class="btn btn-primary btn-download" href="{$url}">Download this Build now</a>
            </div>

            <div class="form-group ui-form-group-image-container">
                <a href="/api/v1/app/code/{$app->getId()}"><img src="/api/v1/app/code/{$app->getId()}" class="ui-qr-code" /></a>
            </div>
        </div>
EOT;
        if($count > 1)
            echo <<<EOT
    <div class="form-group">
        <label>Other previous versions</label>
    </div>
EOT;
    }
}



include('inc/footer.download.html');
?>
