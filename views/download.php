<?php
include('inc/header.html');
?>

<form action="/api/v1/download" method="GET">
    <div class="form-group">
        <label>ID:</label>
        <br />
        <input name="id" value="com.ibm.cio.be.ifundit.platform.mobile" class="form-control" />
        <br /><br />

        <input type="submit" />
    </div>
</form>


<?php
include('inc/footer.html');
?>
