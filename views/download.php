<?php
include('inc/header.html');
$host = $_SERVER['HTTP_HOST'];

echo <<<EOT

<form action="itms-services://?action=download-manifest&amp;url=https://{$host}/api/v1/download/meta" method="GET">
    <div class="form-group">
        <label>ID:</label>
        <br />
        <input name="id" value="com.ibm.cio.be.ifundit.platform.mobile" class="form-control" />
        <br /><br />

        <input type="submit" />
    </div>
</form>

EOT;

include('inc/footer.html');
?>
