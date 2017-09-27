<?php
include('inc/header.php');

$listHTML = '';

if(count($users) > 0) {
    foreach($users as $key => $val) {
        
            $listHTML .= <<<EOT
<li class="list-group-item">
    <a href="/users/{$val['id']}">{$val['display']}</a>
</li>
EOT;
    }
}
else {
    $listHTML .= <<<EOT
    <li class="list-group-item">{$translator->translate('No users')}</li>
EOT;
}


$html = <<<EOT

<div id="t-wrapper" class="users">

    <div class="panel panel-default ui-users">
        <div class="panel-heading">{$translator->translate('Users')} 
            <a href="/users/0" class="ui-right">{$translator->translate('Add a new User')}</a>
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
