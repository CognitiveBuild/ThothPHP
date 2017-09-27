<?php
include('inc/header.php');

$visitorsHTML = '';
foreach($visitors as $visitor) {
    $visitorsHTML .= <<<EOT
<li class="list-group-item">
    <span class="glyphicon glyphicon-user"></span>
    <a href="/visitors/{$visitor['id']}" class="name ui-modal-button">[{$visitor['companyname']}] {$visitor['firstname']}, {$visitor['lastname']}</a>
</li>
EOT;
}

echo <<<EOT
<div id="t-wrapper" class="visitor">

    <div class="panel panel-default">
        <div class="panel-heading">{$translator->translate('Visitor')} <a href="/visitors/0" class="ui-right ui-modal-button" data-id="0">{$translator->translate('Add a new Visitor')}</a></div>
        <div class="panel-body">
            <ul class="list list-group list-group-highlight">
{$visitorsHTML}
            </ul>
            </div>
        </div>
    </div>
EOT;

include('inc/footer.html');
?>
