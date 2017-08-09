<?php
include('inc/header.html');
?>

<div id="t-wrapper" class="event">

    <div class="panel panel-default">
        <div class="panel-heading">Events <a href="/events/0" class="ui-right ui-modal-button" data-id="0">Add a new Event</a></div>
        <div class="panel-body">
            <ul class="list list-group list-group-highlight">
<?php
foreach($events as $event) {
    $visit_date_string = $event['visitdate'];
    $is_active = (isset($event['isactive']) && ($event['isactive'] === OPTION_YES));
    $extra_display = '';
    $status = '<span class="glyphicon glyphicon-remove"></span>';
    if($is_active) {
        $status = '<span class="glyphicon glyphicon-ok"></span>';
    }

    $now_string = date('Y-m-d');

    $now = date_create($now_string);
    $visit_date = date_create($visit_date_string);
    $interval = date_diff($now, $visit_date);
    $count = $interval->format('%R%a');

    if($visit_date_string == $now_string) {
        $extra_display = <<<EOT
<span class="badge badge-red">Today</span>
EOT;
    }
    else {
        if($count > 0) {
            $extra_display = "<span class=\"badge\">{$count} days</span>";
        }
        else {
            $extra_display = "<span class=\"badge badge-green\">Completed</span>";
        }
    }
    echo <<<EOT
<li class="list-group-item">
    {$status}
    <a href="/events/{$event['id']}" class="name ui-modal-button">[{$visit_date_string}] {$event['displayas']}</a>
    {$extra_display}
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
