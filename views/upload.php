<?php
include('inc/header.html');
$uploadHTML = '';
$platformHTML = '';
$regionHTML = '';
$downloadHTML = '';

if($build->getId() === BuildModel::NEW_ID) {
    $platformHTML = '<option value="0">Please choose one</option>';
    $uploadHTML = <<<EOT
<div class="form-group attachment-group">
    <div class="attachments">
        <div class="file-group">
            <label><span class="glyphicon glyphicon-plus"></span></label>
            <input type="file" name="binary[]" class="file-upload-input build-binary-input" />
        </div>
    </div>
</div>
EOT;
}
else {
    $downloadHTML = <<<EOT
    <a class="btn btn-secondary btn-build-download" href="/api/v1/download/{$build->getId()}">Download build</a>
EOT;
    $uploadHTML = <<<EOT

<div class="form-group">
    <img src="/api/v1/download/code/{$build->getId()}" class="ui-qr-code" />
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

$regions = array('Dallas' => 'dallas', 'London' => 'london');
foreach($regions as $label => $val) {

    $selected = '';
    if($val === $build->getRegion()) {
        $selected = ' selected="selected"';
    }

    $regionHTML .= <<<EOT
    <option value="{$val}"{$selected}>{$label}</option>
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
    <input type="hidden" name="id" value="{$build->getId()}" />
    <div class="form-group">
        <label for="name">Name (Prefix)</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="{$build->getName()}" />
    </div>

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
        <label for="region">Region</label>
        <select class="form-control" id="region" name="region">
        {$regionHTML}
        </select>
    </div>

    <div class="form-group">
        <label for="container">Container</label>
        <input type="text" class="form-control" id="container" name="container" placeholder="Container" value="{$build->getContainer()}" />
    </div>

    <div class="form-group">
        <label for="version">Version</label>
        <input type="text" class="form-control" id="version" name="version" placeholder="Version" value="{$build->getVersion()}" />
    </div>

    {$uploadHTML}
EOT;

?>
                <button type="submit" class="btn btn-primary btn-build-save">Save changes</button>
<?php
    echo $downloadHTML;
?>
            </form>
        </div>
    </div>

    
</div>

<?php
include('inc/footer.html');
?>
