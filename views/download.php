<?php
include('inc/header.download.html');
$uid = $build['uid'];
$platform = $build['platform'];
$icon = $platform === 'iOS' ? '<i class="glyphicon glyphicon-apple glyphicon-apple-big"></i>' : ''; //todo: android

$metaLink = DistributionManager::getMetadataLink($build['id']); // todo: android

$url = $platform === 'iOS' ? "itms-services://?action=download-manifest&amp;url={$metaLink}" : ''; //todo: android

$version = $build['version'];
$display = $build['display'];
$time = $build['time'];

echo <<<EOT
<div id="t-wrapper" class="download">
    <div class="form-group">
        <h1>{$display} (v{$version})</h1>
        <p class="ui-note">OTA Install for {$platform}</p>
    </div>

    <div class="form-group">
        {$icon}
    </div>

    <div class="form-group">
        <p class="ui-notice">Published on: {$time}</p>
    </div>

    <div class="form-group">
        <a class="btn btn-primary btn-download" href="{$url}">Download this Build now</a>
    </div>
</div>
EOT;

include('inc/footer.download.html');
?>
