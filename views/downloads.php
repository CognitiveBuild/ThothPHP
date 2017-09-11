<?php
include('inc/header.html');
?>

<div id="t-wrapper" class="list">
<div class="panel panel-default ui-downloads">
        <div class="panel-heading">Builds <a href="/builds/0" class="ui-right ui-modal-button" data-id="0">Add a new Build</a></div>
        <div class="panel-body">
            <ul class="list list-group list-group-highlight">
<?php
foreach($builds as $key => $build) {
    $uid = $build['uid'];
    $platform = $build['platform'];
    $icon = $platform === BuildModel::IOS ? '<i class="glyphicon glyphicon-apple"></i>' : ''; //todo: android

    $url = $url = DistributionManager::getDownloadUrl($build['id']);
    
    $version = $build['version'];
    $display = $build['display'];
    $time = $build['time'];

    echo <<<EOT
    <li class="list-group-item">
        {$icon}
        <a href="/builds/{$build['id']}" class="name ui-modal-button">{$display} (v{$version})</a>
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
