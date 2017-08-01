<?php
include('inc/header.html');
?>

<div class="ui-catalog">

    <div class="panel panel-default ui-catalog-industries">
        <div class="panel-heading">Asset details</div>
        <div class="panel-body">
            <form class="list list-group" method="POST" enctype="multipart/form-data">

<?php
$industryHTML = '';
if($id === 0) {
    $industryHTML = '<option value="0">Please choose one</option>';
}

foreach($industries  as $industry) {

    $selected = '';
    if($industry['id'] === $asset['idindustry']) {
        $selected = ' selected="selected"';
    }

    $industryHTML .= <<<EOT
    <option value="{$industry['id']}"{$selected}>{$industry['name']}</option>
EOT;
}

$technologyHTML = '';
foreach($technologies as $technology) {
    $selected_label = '';
    $selected = '';
    foreach($technologies_applied as $applied) {
        if($technology['id'] === $applied['idcatalog']) {
            $selected = ' checked="checked"';
            $selected_label = ' ui-button-block-selected';
            break;
        }
    }

    $technologyHTML .= <<<EOT
    <label class="checkbox-inline ui-button-block{$selected_label}">
        <input type="checkbox" name="technology[]" value="{$technology['id']}"{$selected} />
        {$technology['name']}
    </label>
EOT;
}

$attachmentHTML = '';
// for
foreach($attachments as $attachment) {
    $attachmentHTML .= <<<EOT
    <div class="file-group ready" style="background-image: url(/api/v1/assets/attachment/{$attachment['id']})" data-id="{$attachment['id']}">
        <div class="file-upload-remove glyphicon glyphicon-remove"></div>
        <label><span class="glyphicon glyphicon-plus"></span></label>
    </div>
EOT;
}

echo <<<EOT
    <input type="hidden" name="id" value="{$id}" />
    <div class="form-group">
        <label for="name">Name</label>
        <input type="text" class="form-control" id="name" name="name" placeholder="Name" value="{$asset['name']}" />
    </div>

    <div class="form-group">
        <label for="industry">Industry</label>
        <select class="form-control" id="industry" name="idindustry">
{$industryHTML}
        </select>
    </div>

    <div class="form-group">
        <label for="industry">Technology</label>
        <div class="form-control form-control-auto-height" id="technology">
{$technologyHTML}
        </div>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea type="text" class="form-control" id="description" name="description" placeholder="Description">{$asset['description']}</textarea>
    </div>

    <div class="form-group">
        <label for="description">Logo URL</label>
        <input type="text" class="form-control" id="logoUrl" name="logourl" placeholder="Logo URL" value="{$asset['logourl']}" />
    </div>

    <div class="form-group">
        <label for="description">Video URL</label>
        <input type="text" class="form-control" id="videoUrl" name="videourl" placeholder="Video URL" value="{$asset['videourl']}" />
    </div>

    <div class="form-group">
        <label for="description">Link URL</label>
        <input type="text" class="form-control" id="linkUrl" name="linkurl" placeholder="Link URL" value="{$asset['linkurl']}" />
    </div>

    <div class="form-group attachment-group">
            <label for="description">Attachments</label>
            <div class="attachments">
                {$attachmentHTML}
                <div class="file-group" data-id="0">
                    <div class="file-upload-remove glyphicon glyphicon-remove"></div>
                    <label><span class="glyphicon glyphicon-plus"></span></label>
                    <input type="file" name="binary[]" class="file-upload-input" />
                </div>

            </div>
    </div>
EOT;

?>
                <button type="submit" class="btn btn-primary btn-asset-save">Save changes</button>
            </form>
        </div>
    </div>

    
</div>

<?php
include('inc/footer.html');
?>
