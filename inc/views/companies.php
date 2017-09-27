<?php
include('inc/header.php');

$html = <<<EOT

<div id="t-wrapper" class="company">

    <div class="panel panel-default ui-company">
        <div class="panel-heading">{$translator->translate('Company')} <a href="/companies/0" class="ui-right ui-modal-button" data-id="0">{$translator->translate('Add a new Company')}</a></div>
        <div class="panel-body">
            <ul class="list list-group list-group-highlight">
EOT;


foreach($companies as $company) {

    $html .= <<<EOT
<li class="list-group-item">
    <span class="glyphicon glyphicon-globe"></span>
    <a href="/companies/{$company['id']}" class="name ui-modal-button">{$company['name']}</a>
</li>
EOT;
}
$html .= <<<EOT
</ul>
</div>
</div>


</div>

EOT;
echo $html;
include('inc/footer.html');
?>
