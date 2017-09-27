<?php
include('inc/header.php');

$roleHTML = '';

if($id == '0') {
    $roleHTML = '<option value="0">Please choose one</option>';
}

foreach($roles as $role) {

    $selected = '';
    if($role['id'] === $user->getRoleId()) {
        $selected = ' selected="selected"';
    }

    $roleHTML .= <<<EOT
<option value="{$role['id']}"{$selected}>{$role['name']}</option>
EOT;
}

$languageOptions = '';
$language = $user->getLanguage();

foreach(CommonUtility::$SUPPORTED_LANGUAGES as $label => $val) {

    $active = '';
    if($val === $language) {
        $active = ' selected="selected"';
    }

    $languageOptions .= <<<EOT
<option value="{$val}"{$active} {$val} {$language}>{$label}</option>
EOT;
}

$activeTime = date('Y-m-d H:i:s', $user->getActiveTime());

echo <<<EOT

<div id="t-wrapper" class="user">

    <div class="panel panel-default ui-user">
        <div class="panel-heading">{$translator->translate('User details')}</div>
        <div class="panel-body">
            <form class="list list-group" method="POST" autocomplete="nope">

    <input type="hidden" name="id" value="{$user->getId()}" />
    <div class="form-group">
        <label for="display">{$translator->translate('Display name')}</label>
        <input type="text" class="form-control" id="display" name="display" placeholder="{$translator->translate('Display name')}" value="{$user->getDisplay()}" />
    </div>

    <div class="form-group">
        <label for="login">{$translator->translate('Login name')}</label>
        <input type="text" class="form-control" id="login" name="login" autocomplete="nope" placeholder="{$translator->translate('Login name')}" value="{$user->getLogin()}" />
    </div>

    <div class="form-group">
        <label for="idrole">{$translator->translate('Role')}</label>
        <select class="form-control" id="idrole" name="idrole">
{$roleHTML}
        </select>
    </div>

    <div class="form-group">
        <label for="passcode">{$translator->translate('Passcode')}</label>
        <input type="password" class="form-control" id="passcode" name="passcode" autocomplete="new-password" placeholder="{$translator->translate('Passcode')}" value="" />
    </div>

    <div class="form-group">
        <label for="token">{$translator->translate('Token')}</label>
        <input type="text" class="form-control" id="token" name="token" placeholder="{$translator->translate('Token')}" readonly="readonly" value="{$user->getToken()}" />
    </div>

    <div class="form-group">
        <label for="activetime">{$translator->translate('Active time')}</label>
        <input type="text" class="form-control" id="activetime" name="activetime" placeholder="{$translator->translate('Active time')}" readonly="readonly" value="{$activeTime}" />
    </div>

    <div class="form-group">
        <label for="language">{$translator->translate('Language')}</label>
        <select name="language" id="language" class="form-control">
{$languageOptions}
        </select>
    </div>

    <button type="submit" class="btn btn-primary btn-user-save">{$translator->translate('Save changes')}</button>

    </form>
    </div>
</div>
</div>

EOT;

include('inc/footer.html');
?>
