<?php
include('inc/header.html');
?>

<div id="t-wrapper" class="company">

    <div class="panel panel-default ui-company">
        <div class="panel-heading">Company <a href="/companies/0" class="ui-right ui-modal-button" data-id="0">Add a new Company</a></div>
        <div class="panel-body">
            <ul class="list list-group list-group-highlight">
<?php
foreach($companies as $company) {

    echo <<<EOT
<li class="list-group-item">
    <a href="/companies/{$company['id']}" class="name ui-modal-button">{$company['name']}</a>
</li>
EOT;
}
?>
            </ul>
        </div>
    </div>

    
</div>

<?php
include('inc/footer.html');
?>
