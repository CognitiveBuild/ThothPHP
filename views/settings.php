<?php
include('inc/header.php');
$languageOptions = '';
$language = Session::init()->getUser()->getLanguage();

foreach(SUPPORTED_LANGUAGES as $label => $val) {

    $active = '';
    if($val === $language) {
        $active = ' selected="selected"';
    }

    $languageOptions .= <<<EOT
<option value="{$val}"{$active}>{$label}</option>
EOT;
}
echo <<<EOT
<div id="t-wrapper" class="settings">
    <div class="panel panel-default ui-distribute">
    <div class="panel-heading">{$translator->translate('Settings')}</div>
        <div class="panel-body">
            <form class="list list-group" method="POST">

                <div class="form-group">
                    <label for="language">{$translator->translate('Language')}</label>
                    <select name="language" id="language" class="form-control">
{$languageOptions}
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-app-save">{$translator->translate('Submit')}</button>

                <div class="form-group">
                    <div class="ui-notice ui-notice-padding ui-notice-small">
{$message}
                    </div>
                </div>
            </form>
            
        </div>
    </div>
</div>
EOT;

include('inc/footer.html');
?>
