<?php
include('inc/header.html');
?>

<div id="t-wrapper" class="event details">

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

$timelineHTML = '';
if($id > 0) {
    $count = count($timelines);

    if($count > 0) {
        $i = 0;
        foreach($timelines as $timeline) {
            $timelineHTML .= <<<EOT
            <div class="form-group-container">
                <div class="form-group">
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" class="form-control form-control-inline" name="timeline_timestart[{$i}]" placeholder="Start time" maxlength="5" value="{$timeline['timestart']}" />
                        </div>

                        <div class="col-md-1">
                            <span class="glyphicon-remove-width-input">to</span>
                        </div>

                        <div class="col-md-5">
                            <input type="text" class="form-control form-control-inline" name="timeline_timeend[{$i}]" placeholder="End time" maxlength="5" value="{$timeline['timeend']}" />
                        </div>

                        <div class="col-md-1">
                            <span class="glyphicon glyphicon-remove glyphicon-remove-width-input timeline-remove" data-id="{$timeline['id']}"></span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <textarea class="form-control" name="timeline_activity[{$i}]" placeholder="Activity">{$timeline['activity']}</textarea>
                </div>
            </div>
EOT;
            $i++;
        }
    }
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
        <label for="isactive">Is active</label>
        <input type="text" class="form-control" id="isactive" name="isactive" placeholder="Is active" value="{$event['isactive']}" />
    </div>

    <div class="form-group form-group-timeline">
        <div class="form-control form-control-auto-height">
            <label for="timeline">Timeline</label>
            <div class="timeline-container">
                {$timelineHTML}
                <div class="form-group form-group-no-data">No timlines.</div>
            </div>
        </div>
    </div>

    <div class="form-group form-group-visitors">
    </div>

EOT;

?>
                <button type="submit" class="btn btn-primary btn-event-save">Save changes</button>

                <button type="button" class="btn btn-secondary btn-event-timeline-add timeline-add">Add timeline</button>
            </form>
        </div>
    </div>

    <div class="ui-template ui-template-timeline">
        <div class="form-group-container">
            <div class="form-group">
                <div class="row">
                    <div class="col-md-5">
                        <input type="text" class="form-control form-control-inline timeline-timestart" data-name="timeline_timestart" placeholder="Start time" maxlength="5" value="" />
                    </div>

                    <div class="col-md-1">
                        <span class="glyphicon-remove-width-input">to</span>
                    </div>

                    <div class="col-md-5">
                        <input type="text" class="form-control form-control-inline timeline-timeend" data-name="timeline_timeend" placeholder="End time" maxlength="5" value="" />
                    </div>

                    <div class="col-md-1">
                        <span class="glyphicon glyphicon-remove glyphicon-remove-width-input timeline-remove" data-id="0"></span>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <textarea class="form-control timeline-activity" data-name="timeline_activity" placeholder="Activity"></textarea>
            </div>
        </div>
    </div>
</div>

<?php
include('inc/footer.html');
?>
