<?php
include('inc/header.download.html');

$icon = $build->getPlatform() === BuildModel::IOS ? '<i class="glyphicon glyphicon-apple glyphicon-apple-big"></i>' : ''; //todo: android

$url = DistributionManager::getDownloadUrl($build->getId());

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
        <p class="ui-notice">Published on: {$build->getTime()}</p>
    </div>

    <div class="form-group">
        <a class="btn btn-primary btn-download" href="{$url}">Download this Build now</a>
    </div>
</div>
EOT;

include('inc/footer.download.html');
?>
