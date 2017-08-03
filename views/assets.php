<?php
include('inc/header.html');
?>

<div id="t-wrapper" class="asset">

    <div class="panel panel-default ui-asset">
        <div class="panel-heading">Assets <a href="/assets/0" class="ui-right ui-modal-button" data-id="0">Add a new Asset</a></div>
        <div class="panel-body">
            <ul class="list list-group list-group-highlight">
<?php
foreach($assets as $asset) {
    $logo_icon = '';
    $file_icon = '';
    $video_icon = '';
    $link_icon = '';

    if(isset($asset['attachments']) && $asset['attachments'] != '') {
        $json_arary = json_decode($asset['attachments'], true);
        if(count($json_arary) > 0) {
            $file_icon = '<span class="glyphicon glyphicon-file glyphicon-icon-right"></span>';
        }
    }
    if($asset['logourl'] != '') {
        $logo_icon = '<span class="glyphicon glyphicon-picture glyphicon-icon-right"></span>';
    }

    if($asset['videourl'] != '') {
        $video_icon = '<span class="glyphicon glyphicon-hd-video glyphicon-icon-right"></span>';
    }

    if($asset['linkurl'] != '') {
        $link_icon = '<span class="glyphicon glyphicon-link glyphicon-icon-right"></span>';
    }

    echo <<<EOT
<li class="list-group-item">
    <a href="/assets/{$asset['id']}" class="name ui-modal-button">{$asset['name']}</a>
    {$file_icon}
    {$link_icon}
    {$logo_icon}
    {$video_icon}
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
