<?php
include('inc/header.php');
$uploadHTML = '';
$platformHTML = '';
$downloadHTML = '';

if($build->getBuildId() === BuildModel::NEW_ID) {
    $platformHTML = <<<EOT
<option value="0">{$translator->translate('Please choose one')}</option>';
EOT;

    $uploadHTML = <<<EOT
<div class="form-group attachment-group">
    <div class="attachments">
        <div class="file-group file-group-build">
            <label><span class="glyphicon glyphicon-plus"></span> <span class="ui-file-label" data-text="{$translator->translate('Choose your Build')}"></span></label>
            <input type="file" name="binary[]" class="file-upload-input build-binary-input" />
        </div>
    </div>
</div>
<button type="submit" class="btn btn-primary btn-build-save">{$translator->translate('Upload the Build')}</button>
EOT;
}
else {
    $downloadHTML = <<<EOT
    <button type="submit" class="btn btn-primary btn-build-delete">{$translator->translate('Update Release notes')}</button>
    <a class="btn btn-info ui-build-distribute" href="/apps/{$idapp}/distribute?id={$build->getBuildId()}">{$translator->translate('Distribute')}</a>
    <a class="btn btn-secondary btn-build-download" href="/api/v1/build/download/{$build->getBuildId()}">{$translator->translate('Download build')}</a>
EOT;

    $uploadHTML = <<<EOT
<div class="form-group">
    <label for="ui-qrcode" class="ui-label-block">{$translator->translate('QR Code')}</label>
    <a href="/api/v1/build/code/{$idapp}" id="ui-qrcode"><img src="/api/v1/build/code/{$idapp}" class="ui-qr-code" /></a>
</div>
EOT;
}

$platforms = [BuildModel::IOS, BuildModel::ANDROID];

foreach($platforms as $platform) {

    $selected = '';
    if($platform === $build->getPlatform()) {
        $selected = ' selected="selected"';
    }

    $platformHTML .= <<<EOT
    <option value="{$platform}"{$selected}>{$platform}</option>
EOT;
}

echo <<<EOT

<div id="t-wrapper" class="build">

    <div class="panel panel-default ui-build">
        <div class="panel-heading">{$translator->translate('Build details')}</div>
        <div class="panel-body">
            <form class="list list-group ui-build-form" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="idbuild" value="{$build->getBuildId()}" />
            <input type="hidden" name="idapp" value="{$app->getId()}" />

            <div class="form-group">
                <label for="display">{$translator->translate('Display name')}</label>
                <input type="text" class="form-control" id="display" name="display" placeholder="{$translator->translate('Display name')}" value="{$build->getDisplay()}" />
            </div>

            <div class="form-group">
                <label for="uid">{$translator->translate('Bundle ID / Package name')}</label>
                <input type="text" class="form-control" id="uid" name="uid" placeholder="{$translator->translate('Bundle ID / Package name')}" value="{$build->getUid()}" />
            </div>

            <div class="form-group">
                <label for="platform">{$translator->translate('Platform')}</label>
                <select class="form-control" id="platform" name="platform">
                {$platformHTML}
                </select>
            </div>

            <div class="form-group">
                <label for="version">{$translator->translate('Version')}</label>
                <input type="text" class="form-control" id="version" name="version" placeholder="{$translator->translate('Version')}" value="{$build->getVersion()}" />
            </div>

            <div class="form-group">
                <label for="uid">{$translator->translate('Release notes')}</label>
                <textarea class="form-control" name="notes" id="notes" placeholder="{$translator->translate('Release notes')}">{$build->getNotes()}</textarea>
            </div>

            {$uploadHTML}

            {$downloadHTML}
            </form>
        </div>
    </div>
</div>

EOT;

include('inc/footer.html');
?>
