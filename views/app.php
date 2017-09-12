<?php
include('inc/header.html');

$regionHTML = '';

$regions = array('Dallas' => 'dallas', 'London' => 'london');
foreach($regions as $label => $val) {

    $selected = '';
    if($val === $app->getRegion()) {
        $selected = ' selected="selected"';
    }

    $regionHTML .= <<<EOT
    <option value="{$val}"{$selected}>{$label}</option>
EOT;
}

?>

<div id="t-wrapper" class="app">

    <div class="panel panel-default ui-app">
        <div class="panel-heading">App details</div>
        <div class="panel-body">
            <form class="list list-group" method="POST" enctype="multipart/form-data">
<?php
echo <<<EOT
    <input type="hidden" name="id" value="{$app->getId()}" />
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="{$app->getName()}" />
    </div>

    <div class="form-group">
        <label for="region">Region</label>
        <select class="form-control" id="region" name="region">
        {$regionHTML}
        </select>
    </div>

    <div class="form-group">
        <label for="container">Container</label>
        <input type="text" class="form-control" id="container" name="container" placeholder="Container" value="{$app->getContainer()}" />
    </div>

EOT;

?>
                <button type="submit" class="btn btn-primary btn-app-save">Save changes</button>

            </form>
        </div>
    </div>
<?php
if($app->getId() > 0) {
echo <<<EOT
<div class="panel panel-default ui-builds">
<div class="panel-heading">Builds <a href="/apps/{$app->getId()}/builds/0" class="ui-right ui-modal-button" data-id="0">Add a new Build</a></div>
<div class="panel-body">
    <ul class="list list-group list-group-highlight">
EOT;
if(count($builds)) {
    foreach($builds as $key => $build) {
        $display = $build['display'];
    
        $icon = $build['platform'] === BuildModel::IOS ? '<i class="glyphicon glyphicon-apple"></i>' : '<i class="glyphicon glyphicon-text-background"></i>';
    
        echo <<<EOT
        <li class="list-group-item">
            {$icon}
            <a href="/apps/{$app->getId()}/builds/{$build['idbuild']}" class="name ui-modal-button">{$display} (v{$build['version']})</a>

            <a class="badge badge-red ui-build-delete" data-idapp="{$app->getId()}" data-idbuild="{$build['idbuild']}" href="#/{$build['idbuild']}">Delete</a>
        </li>
EOT;
    }
}
else {
    echo <<<EOT
    <li class="list-group-item list-group-item-customized ">No builds</li>
EOT;
}
echo <<<EOT
</ul>
</div>
</div>
EOT;
}
?>  
</div>

<?php
include('inc/footer.html');
?>
