<?php
include('inc/header.php');

$aclsHTML = '';
foreach($acls as $key => $val) {
    $checked = '';
    $count = intval($val['count']);
    if($count > 0) {
        $checked = ' checked="checked"';
    }

    $aclsHTML .= <<<EOT
    <li class="list-group-item">
        <div class="checkbox">
        <label>
            <input type="checkbox" name="acls[{$val['id']}]"{$checked} /> 
            {$val['description']}
            </label>
        </div>
    </li>
EOT;
}

echo <<<EOT

<div id="t-wrapper" class="role">
    <form class="list list-group" method="POST" autocomplete="nope">
        <div class="panel panel-default ui-role">
            <div class="panel-heading">{$translator->translate('Role details')}</div>
            <div class="panel-body">
                <input type="hidden" name="id" value="{$role->getId()}" />
                <div class="form-group">
                    <label for="name">{$translator->translate('Name')}</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="{$translator->translate('Name')}" value="{$role->getName()}" />
                </div>

                <div class="form-group">
                    <label for="description">{$translator->translate('Description')}</label>
                    <textarea class="form-control" name="description" id="description" placeholder="{$translator->translate('Description')}">{$role->getDescription()}</textarea>
                </div>
            </div>
        </div>


        <div class="panel panel-default ui-acls">
            <div class="panel-heading">{$translator->translate('ACLs')}</div>
            <div class="panel-body">
                <ul class="list list-group">
                {$aclsHTML}
                </ul>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-user-save">{$translator->translate('Save changes')}</button>
    </form>
</div>

EOT;

include('inc/footer.html');
?>
