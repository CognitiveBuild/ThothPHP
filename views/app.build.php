<?php
include('inc/header.html');
$uploadHTML = '';
$platformHTML = '';
$downloadHTML = '';

if($build->getBuildId() === BuildModel::NEW_ID) {
    $platformHTML = '<option value="0">Please choose one</option>';
    $uploadHTML = <<<EOT
<div class="form-group attachment-group">
    <div class="attachments">
        <div class="file-group file-group-build">
            <label><span class="glyphicon glyphicon-plus"></span> <span class="ui-file-label" data-text="Choose your Build"></span></label>
            <input type="file" name="binary[]" class="file-upload-input build-binary-input" />
        </div>
    </div>
</div>
<button type="submit" class="btn btn-primary btn-build-save">Upload the Build</button>
EOT;
}
else {
    $downloadHTML = <<<EOT
    <button type="submit" class="btn btn-danger btn-build-delete">Delete this Build</button>
    <a class="btn btn-secondary btn-build-download" href="/api/v1/build/download/{$build->getBuildId()}">Download build</a>
EOT;

    $uploadHTML = <<<EOT
<div class="form-group">
    <a href="/api/v1/build/code/{$idapp}"><img src="/api/v1/build/code/{$idapp}" class="ui-qr-code" /></a>
</div>
EOT;
}

$platforms = array(BuildModel::IOS, BuildModel::ANDROID);

foreach($platforms as $platform) {

    $selected = '';
    if($platform === $build->getPlatform()) {
        $selected = ' selected="selected"';
    }

    $platformHTML .= <<<EOT
    <option value="{$platform}"{$selected}>{$platform}</option>
EOT;
}

?>

<div id="t-wrapper" class="build">

    <div class="panel panel-default ui-build">
        <div class="panel-heading">Build details</div>
        <div class="panel-body">
            <form class="list list-group" method="POST" enctype="multipart/form-data">
<?php
echo <<<EOT
    <input type="hidden" name="idbuild" value="{$build->getBuildId()}" />
    <input type="hidden" name="idapp" value="{$app->getId()}" />

    <div class="form-group">
        <label for="display">Display name</label>
        <input type="text" class="form-control" id="display" name="display" placeholder="Display name" value="{$build->getDisplay()}" />
    </div>

    <div class="form-group">
        <label for="uid">Bundle ID / Package name</label>
        <input type="text" class="form-control" id="uid" name="uid" placeholder="Bundle ID / Package name" value="{$build->getUid()}" />
    </div>

    <div class="form-group">
        <label for="platform">Platform</label>
        <select class="form-control" id="platform" name="platform">
        {$platformHTML}
        </select>
    </div>

    <div class="form-group">
        <label for="version">Version</label>
        <input type="text" class="form-control" id="version" name="version" placeholder="Version" value="{$build->getVersion()}" />
    </div>

    {$uploadHTML}
EOT;

    echo $downloadHTML;
?>
            </form>
        </div>
    </div>

    
</div>

<?php
include('inc/footer.html');
?>
