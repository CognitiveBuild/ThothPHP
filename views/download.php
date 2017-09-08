<?php
include('inc/header.html');

echo <<<EOT
<div id="t-wrapper" class="download">
    <div class="form-group">
        <label>ID:</label>
        <br />
        <input type="text" name="id" value="{$id}" class="form-control bundle-id" />
        <input type="hidden" name="bundle-host" value="{$host}" class="bundle-host" />
        <br /><br />

        <a class="btn btn-primary btn-download" href="itms-services://?action=download-manifest&amp;url=https://{$host}/api/v1/download/meta?id={$id}">Download {$id}</a>
    </div>
</div>
EOT;

include('inc/footer.html');
?>
