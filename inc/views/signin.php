<?php
include('inc/header.public.php');

echo <<<EOT

<div id="t-wrapper" class="signin">
<h1>{$translator->translate('Sign In')}</h1>

<form method="post" action="/">
    <div class="form-group">
        <label for="login">{$translator->translate('Username')}</label>
        <input type="text" class="form-control" id="login" name="login" placeholder="{$translator->translate('Username')}" value="{$login}" />
    </div>
    <div class="form-group">
        <label for="passcode">{$translator->translate('Passcode')}</label>
        <input type="password" class="form-control" id="passcode" name="passcode" placeholder="{$translator->translate('Passcode')}" value="{$passcode}" />
    </div>

    <div class="form-group">
        <div class="ui-notice">{$message}</div>
    </div>

    <input type="submit" class="btn btn-primary btn-event-save" value="{$translator->translate('Submit')}" />
</form>
</div>

EOT;

include('inc/footer.public.html');
?>
