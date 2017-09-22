<?php
include('inc/header.php');
$buildHTML = '';
$submitHTML = '';
$result = '';
$count = count($builds);

if($status) {
    $result = $translator->translate('You have sucessfully distributed the Build to: <br /><br /><strong>%s</strong>', [ $emails ]);
}

if($count > 0) {
    foreach($builds as $key => $build) {
        $selected = '';
        if($idbuild === $build['idbuild']) {
            $selected = ' selected="selected"';
        }
        $buildHTML .= <<<EOT
    <option value="{$build['idbuild']}"{$selected}>[{$build['platform']}] {$build['display']} - v{$build['version']}</option>
EOT;
    }

    $submitHTML = <<<EOT
    <button type="submit" class="btn btn-primary">{$translator->translate('Distribute')}</button>
EOT;
}
else {
    $buildHTML = <<<EOT
    <option>No builds available.</option>
EOT;
}


echo <<<EOT

<div id="t-wrapper" class="distribute">

    <div class="panel panel-default ui-distribute">
        <div class="panel-heading">{$translator->translate('Distribution')}</div>
        <div class="panel-body">
            <form class="list list-group ui-distribute-form" method="POST">
                <input type="hidden" name="idapp" value="{$app->getId()}" />

                <div class="form-group">
                    <label for="idbuild">{$translator->translate('Build')}</label>
                    <select class="form-control" id="idbuild" name="idbuild">
{$buildHTML}
                    </select>
                </div>

                <div class="form-group">
                    <label for="uid">{$translator->translate('Emails to be sent')}</label>
                    <textarea class="form-control" name="emails" id="emails">{$emails}</textarea>

                    <div class="ui-notice ui-notice-padding ui-notice-small">
                        {$translator->translate('_email_format_sample_text')}
                    </div>
                </div>

                <div class="form-group">
                    <label for="uid">{$translator->translate('Message to the users')}</label>
                    <textarea class="form-control" name="message" id="message">{$message}</textarea>
                </div>

{$submitHTML}

                <div class="ui-notice ui-notice-padding ui-notice-small">
{$result}
                </div>
            </form>
        </div>
    </div>
</div>

EOT;

include('inc/footer.html');
?>
