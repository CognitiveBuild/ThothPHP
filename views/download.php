<?php
include('inc/header.download.html');

echo <<<EOT
<div id="t-wrapper" class="download">
    <div class="form-group">
        <h1>OTA Install for iOS</h1>
        <p class="ui-note">{$id}</p>
    </div>

    <div class="form-group">
        <i class="glyphicon glyphicon-apple glyphicon-apple-big"></i>
    </div>

    <div class="form-group">
        <a class="btn btn-primary btn-download" href="itms-services://?action=download-manifest&amp;url={$url}">Download this Build now</a>
    </div>
</div>
EOT;

include('inc/footer.download.html');
?>
