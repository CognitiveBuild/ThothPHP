<?php
include('inc/header.php');

$html = <<<EOT

<div id="t-wrapper" class="catalog">

    <div class="panel panel-default ui-catalog">
        <div class="panel-heading">{$translator->translate('Catalog')} 
            <a href="#" class="ui-right ui-modal-button-catalog" data-toggle="modal" data-target=".modal-catalog" data-type="{$type}" data-id="0" data-name="" data-language="zh-cn">{$translator->translate('Add a new Catalog')}</a>
        </div>
        <div class="panel-body">
            <div class="btn-group btn-group-justified btn-group-language" role="group" aria-label="">
EOT;

foreach(CommonUtility::$SUPPORTED_LANGUAGES as $label => $val) {

    $active = '';
    if($val === $language) {
        $active = ' active';
    }

    $html .= <<<EOT
<a href="?language={$val}" class="btn btn-default{$active}" role="button">{$label}</a>
EOT;
}
$html .= <<<EOT
            </div>
            <ul class="list list-group">
EOT;

    foreach($catalogs as $key => $val) {
        $html .= <<<EOT

<li class="list-group-item">
    <a href="#" class="ui-modal-button-catalog" data-target=".modal-catalog" data-toggle="modal" data-id="{$val['id']}" data-language="{$val['language']}" data-type="{$type}" data-name="{$val['name']}">{$val['name']}</a>
    <span class="badge badge-dst" ref="data-id">{$val['language']}</span>
    <span class="badge" ref="data-id">{$val['count']}</span>
</li>

EOT;
    }
    $html .= <<<EOT
            </ul>
        </div>
    </div>
</div>

<!-- catalog template -->
<div class="modal fade bs-example-modal-lg modal-catalog" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">{$translator->translate('Catalog')}</h4>
    </div>
    <div class="modal-body">
        <div class="form-group form-group-id">
            <label for="type">{$translator->translate('Type')}</label>
            <input type="text" readonly="readonly" class="form-control" id="_type" placeholder="Type" value="{$type}" />

            </div>
            <div class="form-group form-group-id">
                <label for="_id">{$translator->translate('ID')}</label>
                <input type="number" readonly="readonly" class="form-control" id="_id" placeholder="{$translator->translate('ID')}" />
            </div>
            <div class="form-group">
                <label for="_language">{$translator->translate('Language')}</label>
                <select id="_language" name="_language" class="form-control">
    
EOT;
foreach(CommonUtility::$SUPPORTED_LANGUAGES as $label => $language) {
    $html .= <<<EOT
<option value="{$language}">{$label}</option>
EOT;
}
$html .= <<<EOT
                </select>
            </div>
            <div class="form-group">
                <label for="_name">{$translator->translate('Name')}</label>
                <input type="text" class="form-control" id="_name" placeholder="{$translator->translate('Name')}" />
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{$translator->translate('Close')}</button>
            <button type="button" class="btn btn-primary btn-catalog-save">{$translator->translate('Save changes')}</button>
        </div>
    </div>
</div>
</div>
<!-- /technology template -->
EOT;

echo $html;

include('inc/footer.html');
?>
