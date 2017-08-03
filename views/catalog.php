<?php
include('inc/header.html');

?>

<div class="ui-catalog">

    <div class="panel panel-default ui-catalog-industries">
        <div class="panel-heading">Catalog 
<?php
echo <<<EOT
<a href="#" class="ui-right ui-modal-button-catalog" data-toggle="modal" data-target=".modal-catalog" data-type="{$type}" data-id="0" data-name="">Add a new Catalog</a>
EOT;
?>
        </div>
        <div class="panel-body">
            <ul class="list list-group">
<?php
    foreach($catalogs as $key => $val) {
echo <<<EOT

<li class="list-group-item">
    <a href="#" class="ui-modal-button-catalog" data-target=".modal-catalog" data-toggle="modal" data-id="{$val['id']}" data-type="{$type}" data-name="{$val['name']}">{$val['name']}</a>
    <span class="badge" ref="data-id">{$val['count']}</span>
</li>

EOT;
    }
?>

            </ul>
        </div>
    </div>
</div>


<!-- catalog template -->
<div class="modal fade bs-example-modal-lg modal-catalog" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Catalog</h4>
    </div>
    <div class="modal-body">
        <div class="form-group form-group-id">
            <label for="type">Type</label>
<?php 
echo <<<EOT
<input type="text" readonly="readonly" class="form-control" id="_type" placeholder="Type" value="{$type}" />
EOT;
?>
        </div>
        <div class="form-group form-group-id">
            <label for="_id">ID</label>
            <input type="number" readonly="readonly" class="form-control" id="_id" placeholder="ID" />
        </div>
        <div class="form-group">
            <label for="_name">Name</label>
            <input type="text" class="form-control" id="_name" placeholder="Name" />
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary btn-catalog-save">Save changes</button>
    </div>
    </div>
</div>
</div>
<!-- /technology template -->

<?php

include('inc/footer.html');
?>
