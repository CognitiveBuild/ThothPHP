<?php
include('inc/header.html');

echo <<<EOT
<div id="t-wrapper" class="download">
<form action="itms-services://?action=download-manifest&amp;url=https://{$host}/api/v1/download/meta" method="GET">
    <div class="form-group">
        <label>ID:</label>
        <br />
        <input name="id" value="{$id}" class="form-control bundle-id" />
        <br /><br />

        <input type="submit" class="btn btn-primary btn-download" />
    </div>
</form>
</div>
EOT;

include('inc/footer.html');
?>
