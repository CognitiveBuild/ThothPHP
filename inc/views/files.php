<?php
include('inc/header.php');
$html = <<<EOT

<div id="t-wrapper" class="files">

    <div class="panel panel-default">
        <div class="panel-heading">{$translator->translate('files')} <a href="/files/0" class="ui-right ui-modal-button" data-id="0">{$translator->translate('Add a new File')}</a></div>
        <div class="panel-body">
            <ul class="list list-group list-group-highlight">
EOT;

foreach($files as $file) {
    $id = $file['id'];
    $name = $file['name'];
    $size = $file['size'];
    $extra_display = "<span class=\"badge\">{$size} bytes</span>";
    $imageTag = "<img src=\"/api/v1/assets/attachment/{$id}\" width=\"200\" />";
    $html .= <<<EOT
<li class="list-group-item">
    {$imageTag}
    <a href="/files/{$file['id']}" class="name ui-modal-button">{$name}</a>
    {$extra_display}
</li>
EOT;

}
$html .= <<<EOT
</ul>

<div class="ui-pager">
{$pager}
</div>

</div>
</div>
</div>
EOT;

echo $html;

include('inc/footer.html');
?>
