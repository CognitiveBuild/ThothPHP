<?php
include('inc/header.php');

$listHTML = '';

foreach($apps as $key => $app) {    
    $name = $app['name'];

    $distributeHTML = '';

    $count = $app['count'];
    if($count > 0) {
        $distributeHTML = <<<EOT
<a class="badge no-badge badge-dst ui-build-distribute" href="/apps/{$app['id']}/distribute">{$translator->translate('Distribute')}</a>
EOT;
    }

    $listHTML .= <<<EOT
    <li class="list-group-item">
        <a href="/apps/{$app['id']}" class="name ui-modal-button">{$name}</a>
{$distributeHTML}
        <a class="badge">{$app['count']}</a>
    </li>
EOT;
}

echo <<<EOT
<div id="t-wrapper" class="list">
<div class="panel panel-default ui-app">
    <div class="panel-heading">{$translator->translate('Apps')} <a href="/apps/0" class="ui-right ui-modal-button" data-id="0">{$translator->translate('Add a new App')}</a></div>
    <div class="panel-body">
        <ul class="list list-group list-group-highlight">
{$listHTML}
        </ul>
    </div>
</div>

</div>
EOT;

include('inc/footer.html');
?>
