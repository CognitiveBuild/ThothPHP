<?php
include('inc/header.html');
$buildHTML = '';
$result = '';
if($status) {
    $result = "You have sucessfully distributed the Build to {$emails}";
}

foreach($builds as $key => $build) {
    $selected = '';
    if($idbuild === $build['idbuild']) {
        $selected = ' selected="selected"';
    }
    $buildHTML .= <<<EOT
<option value="{$build['idbuild']}"{$selected}>[{$build['platform']}] {$build['display']} - v{$build['version']}</option>
EOT;
}
?>

<div id="t-wrapper" class="distribute">

    <div class="panel panel-default ui-distribute">
        <div class="panel-heading">Distribution</div>
        <div class="panel-body">
            <form class="list list-group ui-distribute-form" method="POST">
<?php
echo <<<EOT
    <input type="hidden" name="idapp" value="{$app->getId()}" />

    <div class="form-group">
        <label for="idbuild">Build</label>
        <select class="form-control" id="idbuild" name="idbuild">
        {$buildHTML}
        </select>
    </div>

    <div class="form-group">
        <label for="uid">Emails to be sent</label>
        <textarea class="form-control" name="emails" id="emails">{$emails}</textarea>

        <div class="ui-notice ui-notice-padding ui-notice-small">
            <div>The formatting of this string must comply with <a href="http://www.faqs.org/rfcs/rfc2822" target="_blank">» RFC 2822</a>. Some examples are:</div>

            user@example.com<br />
            user@example.com, anotheruser@example.com<br />
        </div>
    </div>

    <div class="form-group">
        <label for="uid">Message to the users</label>
        <textarea class="form-control" name="message" id="message">{$message}</textarea>
    </div>

    <button type="submit" class="btn btn-primary">Distribute</button>

    <div class="ui-notice ui-notice-padding ui-notice-small">
    {$result}
    </div>


EOT;
?>
            </form>
        </div>
    </div>

    
</div>

<?php
include('inc/footer.html');
?>
