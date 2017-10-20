<?php
include('inc/header.php');

$industryHTML = '';

foreach($industries  as $industry) {

    $selected = '';
    if($industry['id'] === $company['idindustry']) {
        $selected = ' selected="selected"';
    }

    $industryHTML .= <<<EOT
    <option value="{$industry['id']}"{$selected}>{$industry['name']}</option>
EOT;
}

$logoReadyState = '';
$logoAddButton = '';
if($company['logo'] === NULL) {
    $logoAddButton = '<span class="glyphicon glyphicon-plus"></span>';
}
else {
    $logoReadyState = ' ready';
}

$html = <<<EOT
<div id="t-wrapper" class="company">

    <div class="panel panel-default ui-company">
        <div class="panel-heading">{$translator->translate('Company details')}</div>
        <div class="panel-body">
            <form class="list list-group" method="POST" enctype="multipart/form-data">

    <input type="hidden" name="id" value="{$id}" />
    <div class="form-group">
        <label for="name">{$translator->translate('Name')}</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="{$translator->translate('Name')}" value="{$company['name']}" />
    </div>

    <div class="form-group">
        <label for="industry">{$translator->translate('Industry')}</label>
        <select class="form-control" id="industry" name="idindustry">
{$industryHTML}
        </select>
    </div>

    <div class="form-group">
        <label for="description">{$translator->translate('Description')}</label>
        <textarea type="text" class="form-control" id="description" name="description" placeholder="{$translator->translate('Description')}">{$company['description']}</textarea>
    </div>

    <div class="form-group attachment-group">
            <label for="description">{$translator->translate('Logo')}</label>
            <div class="attachments">
                <div class="file-group{$logoReadyState}" style="background-image: url(/api/v1/companies/logo/{$company['id']})" data-id="{$company['id']}">
                    <div class="company-logo-remove glyphicon glyphicon-remove"></div>
                    <label>{$logoAddButton}</label>
                    <input type="file" name="logo[]" class="company-logo-input" />
                </div>
            </div>
    </div>
    <button type="submit" class="btn btn-primary btn-company-save">{$translator->translate('Save changes')}</button>
    </form>
</div>
</div>

</div>

EOT;

echo $html;

include('inc/footer.html');
?>
