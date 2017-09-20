<?php
include('inc/header.php');

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

$existHTML = '';
if($app->getId() > 0) {
    $existHTML .= <<<EOT
        <div class="panel panel-default ui-builds">
        <div class="panel-heading">{$translator->translate('Builds')} <a href="/apps/{$app->getId()}/builds/0" class="ui-right ui-modal-button" data-id="0">{$translator->translate('Add a new Build')}</a></div>
        <div class="panel-body">
            <ul class="list list-group list-group-highlight">
EOT;

if(count($builds)) {
    foreach($builds as $key => $build) {
        $display = $build['display'];
    
        $icon = $build['platform'] === BuildModel::IOS ? '<i class="glyphicon glyphicon-apple"></i>' : '<i class="glyphicon glyphicon-text-background"></i>';
    
        $existHTML .= <<<EOT
        <li class="list-group-item">
            {$icon}
            <a href="/apps/{$app->getId()}/builds/{$build['idbuild']}" class="name ui-modal-button">{$display} (v{$build['version']})</a>

            <a class="badge no-badge badge-dst ui-build-distribute" data-idapp="{$app->getId()}" data-idbuild="{$build['idbuild']}" href="/apps/{$app->getId()}/distribute?id={$build['idbuild']}">{$translator->translate('Distribute')}</a>
            <a class="badge no-badge ui-build-delete" data-idapp="{$app->getId()}" data-idbuild="{$build['idbuild']}" href="#/{$build['idbuild']}">{$translator->translate('Delete')}</a>
        </li>
EOT;
    }
}
else {
    $existHTML .= <<<EOT
    <li class="list-group-item list-group-item-customized ">{$translator->translate('No builds')}</li>
EOT;
}

$existHTML .= <<<EOT
</ul>
</div>
</div>
EOT;
}

echo <<<EOT

<div id="t-wrapper" class="app">

    <div class="panel panel-default ui-app">
        <div class="panel-heading">{$translator->translate('App details')}</div>
        <div class="panel-body">
            <form class="list list-group" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="{$app->getId()}" />
                <div class="form-group">
                    <label for="name">{$translator->translate('Name')}</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="{$translator->translate('Name')}" value="{$app->getName()}" />
                </div>

                <div class="form-group">
                    <label for="region">{$translator->translate('Region')}</label>
                    <select class="form-control" id="region" name="region">
                    {$regionHTML}
                    </select>
                </div>

                <div class="form-group">
                    <label for="container">{$translator->translate('Container')}</label>
                    <input type="text" class="form-control" id="container" name="container" placeholder="{$translator->translate('Container')}" value="{$app->getContainer()}" />
                </div>

                <button type="submit" class="btn btn-primary btn-app-save">{$translator->translate('Submit')}</button>
            </form>
        </div>
    </div>

{$existHTML}

</div>
EOT;

include('inc/footer.html');
?>
