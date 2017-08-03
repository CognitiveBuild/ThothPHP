<?php
include('inc/header.html');
?>

<div id="t-wrapper" class="visitor">

    <div class="panel panel-default">
        <div class="panel-heading">Visitor <a href="/visitors/0" class="ui-right ui-modal-button" data-id="0">Add a new Visitor</a></div>
        <div class="panel-body">
            <ul class="list list-group list-group-highlight">
<?php
foreach($visitors as $visitor) {

    echo <<<EOT
<li class="list-group-item">
    <span class="glyphicon glyphicon-user"></span>
    <a href="/visitors/{$visitor['id']}" class="name ui-modal-button">{$visitor['firstname']}, {$visitor['lastname']}</a>
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
