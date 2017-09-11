<?php
include('inc/header.html');
?>

<div id="t-wrapper" class="list">
    <div class="panel panel-default ui-app">
        <div class="panel-heading">Apps <a href="/apps/0" class="ui-right ui-modal-button" data-id="0">Add a new App</a></div>
        <div class="panel-body">
            <ul class="list list-group list-group-highlight">
<?php
foreach($apps as $key => $app) {    
    $name = $app['name'];

    echo <<<EOT
    <li class="list-group-item">
        <a href="/apps/{$app['id']}" class="name ui-modal-button">{$name}</a>
    </li>
EOT;
}
?>
            </ul>
        </div>
    </div>

</div>
<?php
include('inc/footer.html');
?>
