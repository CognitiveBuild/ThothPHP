<?php
include('inc/header.html');

echo <<<EOT
<div id="t-wrapper" class="download">
    <div class="form-group">
        <label>ID:</label>
        <br />
        <input name="id" value="{$id}" class="form-control bundle-id" />
        <br /><br />

        <input type="submit" class="btn btn-primary btn-download" />
    </div>
</div>
EOT;

include('inc/footer.html');
?>
