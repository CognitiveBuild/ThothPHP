<?php
include('inc/header.html');
?>

<div id="t-wrapper" class="build">

    <div class="panel panel-default ui-build">
        <div class="panel-heading">Build details</div>
        <div class="panel-body">
            <form class="list list-group" method="POST" enctype="multipart/form-data">
<?php
$uploadHTML = '';
$platformHTML = '';
$regionHTML = '';
$downloadHTML = <<<EOT
<a class="btn btn-secondary btn-build-download" href="/api/v1/download/{$id}">Download build</a>
EOT;

if($id == '0') {
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
    $downloadHTML = '';

}

$platforms = array('iOS', 'Android');

foreach($platforms as $platform) {

    $selected = '';
    if($platform === $build['platform']) {
        $selected = ' selected="selected"';
    }

    $platformHTML .= <<<EOT
    <option value="{$platform}"{$selected}>{$platform}</option>
EOT;
}

$regions = array('Dallas' => 'dallas', 'London' => 'london');
foreach($regions as $label => $val) {

    $selected = '';
    if($val === $build['region']) {
        $selected = ' selected="selected"';
    }

    $regionHTML .= <<<EOT
    <option value="{$val}"{$selected}>{$label}</option>
EOT;
}

echo <<<EOT
    <input type="hidden" name="id" value="{$id}" />
    <div class="form-group">
        <label for="name">Name (Prefix)</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="{$build['name']}" />
    </div>

    <div class="form-group">
        <label for="display">Display name</label>
        <input type="text" class="form-control" id="display" name="display" placeholder="Display name" value="{$build['display']}" />
    </div>

    <div class="form-group">
        <label for="uid">Bundle ID / Package name</label>
        <input type="text" class="form-control" id="uid" name="uid" placeholder="Bundle ID / Package name" value="{$build['uid']}" />
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
        <input type="text" class="form-control" id="container" name="container" placeholder="Container" value="{$build['container']}" />
    </div>

    <div class="form-group">
        <label for="version">Version</label>
        <input type="number" class="form-control" id="version" name="version" placeholder="Version" value="{$build['version']}" />
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
