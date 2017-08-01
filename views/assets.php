<?php
include('inc/header.html');
?>

<div class="ui-catalog">

    <div class="panel panel-default ui-catalog-industries">
        <div class="panel-heading">Assets <a href="/assets/0" class="ui-right ui-modal-button" data-id="0">Add a new Asset</a></div>
        <div class="panel-body">
            <ul class="list list-group">
<?php
foreach($assets as $asset) {
    echo <<<EOT
<li class="list-group-item">
    <span class="id">{$asset['id']}</span>
    <a href="/assets/{$asset['id']}" class="name ui-modal-button">{$asset['name']}</a>
    <span class="badge no-badge" data-id="/api/v1/assets/{$asset['id']}">Delete</span>
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
