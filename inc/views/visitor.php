<?php
include('inc/header.php');

$companyHTML = '';
if($id == '0') {
    $companyHTML = <<<EOT
<option value="0">{$translator->translate('Please choose one')}</option>
EOT;
}

foreach($companies as $company) {

    $selected = '';
    if($company['id'] === $visitor['idcompany']) {
        $selected = ' selected="selected"';
    }

    $companyHTML .= <<<EOT
    <option value="{$company['id']}"{$selected}>{$company['name']}</option>
EOT;
}

$avatarReadyState = '';
$avatarAddButton = '';
if($visitor['avatar'] === NULL) {
    $avatarAddButton = '<span class="glyphicon glyphicon-plus"></span>';
}
else {
    $avatarReadyState = ' ready';
}


echo <<<EOT
<div id="t-wrapper" class="visitor">

    <div class="panel panel-default ui-visitor">
        <div class="panel-heading">{$translator->translate('Visitor details')}</div>
        <div class="panel-body">
            <form class="list list-group" method="POST" enctype="multipart/form-data">

    <input type="hidden" name="id" value="{$id}" />
    <div class="form-group">
        <label for="firstname">{$translator->translate('First name')}</label>
        <input type="text" class="form-control" id="firstname" name="firstname" placeholder="{$translator->translate('First name')}" value="{$visitor['firstname']}" />
    </div>

    <div class="form-group">
        <label for="lastname">{$translator->translate('Last name')}</label>
        <input type="text" class="form-control" id="lastname" name="lastname" placeholder="{$translator->translate('Last name')}" value="{$visitor['lastname']}" />
    </div>

    <div class="form-group">
        <label for="company">{$translator->translate('Company')}</label>
        <select class="form-control" id="company" name="idcompany">
{$companyHTML}
        </select>
    </div>

    <div class="form-group">
        <label for="website">{$translator->translate('Website')}</label>
        <input type="text" class="form-control" id="website" name="website" placeholder="{$translator->translate('Website URL')}" value="{$visitor['website']}" />
    </div>

    <div class="form-group">
        <label for="linkedin">{$translator->translate('Linked-In')}</label>
        <input type="text" class="form-control" id="linkedin" name="linkedin" placeholder="{$translator->translate('Linked-In URL')}" value="{$visitor['linkedin']}" />
    </div>

    <div class="form-group">
        <label for="facebook">{$translator->translate('Facebook')}</label>
        <input type="text" class="form-control" id="facebook" name="facebook" placeholder="{$translator->translate('Facebook URL')}" value="{$visitor['facebook']}" />
    </div>

    <div class="form-group">
        <label for="twitter">{$translator->translate('Twitter')}</label>
        <input type="text" class="form-control" id="twitter" name="twitter" placeholder="{$translator->translate('Twitter URL')}" value="{$visitor['twitter']}" />
    </div>

    <div class="form-group">
        <label for="order">{$translator->translate('Order')}</label>
        <input type="number" class="form-control" id="order" name="order" placeholder="Order" value="{$visitor['order']}" />
    </div>

    <div class="form-group attachment-group">
            <label for="description">{$translator->translate('Avatar')}</label>
            <div class="attachments">
                <div class="file-group{$avatarReadyState}" style="background-image: url(/api/v1/visitor/avatar/{$visitor['id']})" data-id="{$visitor['id']}">
                    <div class="visitor-avatar-remove glyphicon glyphicon-remove"></div>
                    <label>{$avatarAddButton}</label>
                    <input type="file" name="avatar[]" class="visitor-avatar-input" />
                </div>
            </div>
    </div>
    <button type="submit" class="btn btn-primary btn-visitor-save">{$translator->translate('Save changes')}</button>
    </form>
</div>
</div>


</div>
EOT;

include('inc/footer.html');
?>
