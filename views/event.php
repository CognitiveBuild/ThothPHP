<?php
include('inc/header.html');
?>

<div id="t-wrapper" class="event">

    <div class="panel panel-default ui-event">
        <div class="panel-heading">Event details</div>
        <div class="panel-body">
            <form class="list list-group" method="POST">

<?php
$companyHTML = '';
if($id == '0') {
    $companyHTML = '<option value="0">Please choose one</option>';
}

foreach($companies as $company) {

    $selected = '';
    if($company['id'] === $event['idcompany']) {
        $selected = ' selected="selected"';
    }

    $companyHTML .= <<<EOT
    <option value="{$company['id']}"{$selected}>{$company['name']}</option>
EOT;
}

echo <<<EOT
    <input type="hidden" name="id" id="id" value="{$id}" />
    <div class="form-group">
        <label for="visitdate">Visit date</label>
        <input type="text" class="form-control datepicker" id="visitdate" name="visitdate" placeholder="Visit date" value="{$event['visitdate']}" />
    </div>

    <div class="form-group">
        <label for="lastname">Display as</label>
        <input type="text" class="form-control" id="displayas" name="displayas" placeholder="Display as" value="{$event['displayas']}" />
    </div>

    <div class="form-group">
        <label for="company">Company</label>
        <select class="form-control" id="company" name="idcompany">
{$companyHTML}
        </select>
    </div>

    <div class="form-group">
        <label for="linkedin">Is active</label>
        <input type="text" class="form-control" id="isactive" name="isactive" placeholder="Is active" value="{$event['isactive']}" />
    </div>

    <div class="form-group form-group-visitors">
    </div>

EOT;

?>
                <button type="submit" class="btn btn-primary btn-visitor-save">Save changes</button>
            </form>
        </div>
    </div>

    
</div>

<?php
include('inc/footer.html');
?>
