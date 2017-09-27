<?php
include('inc/header.php');

$listHTML = '';

if(count($roles) > 0) {
    foreach($roles as $key => $val) {
        
            $listHTML .= <<<EOT
<li class="list-group-item">
    <a href="/roles/{$val['id']}">{$val['name']}</a>
</li>
EOT;
    }
}
else {
    $listHTML .= <<<EOT
    <li class="list-group-item">{$translator->translate('No roles')}</li>
EOT;
}

$html = <<<EOT

<div id="t-wrapper" class="roles">

    <div class="panel panel-default ui-roles">
        <div class="panel-heading">{$translator->translate('Roles')} 
            <a href="/roles/0" class="ui-right">{$translator->translate('Add a new Role')}</a>
        </div>
        <div class="panel-body">
            <ul class="list list-group">
            {$listHTML}
            </ul>
        </div>
    </div>
</div>
EOT;

echo $html;

include('inc/footer.html');
?>
