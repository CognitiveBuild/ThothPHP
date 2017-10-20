<?php
include('inc/header.php');

$industryHTML = <<<EOT
    <option value="0">{$translator->translate('--- Cross industries ---')}</option>
EOT;

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
    <label class="ui-button-block{$selected_label}">
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
<div id="t-wrapper" class="asset">

    <div class="panel panel-default ui-asset">
        <div class="panel-heading">{$translator->translate('Asset details')} - {$language}</div>
        <div class="panel-body">
            <form class="list list-group" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="id" value="{$id}" />
            <input type="hidden" name="language" value="{$language}" />
            <div class="form-group">
                <label for="name">{$translator->translate('Name')}</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="{$translator->translate('Name')}" value="{$asset['name']}" />
            </div>

            <div class="form-group">
                <label for="industry">{$translator->translate('Industry')}</label>
                <select class="form-control" id="industry" name="idindustry">
{$industryHTML}
                </select>
            </div>

            <div class="form-group">
                <label for="industry">{$translator->translate('Technology')}</label>
                <div class="form-control form-control-auto-height" id="technology">
{$technologyHTML}
                </div>
            </div>

            <div class="form-group">
                <label for="description">{$translator->translate('Description')}</label>
                <textarea type="text" class="form-control" id="description" name="description" placeholder="{$translator->translate('Description')}">{$asset['description']}</textarea>
            </div>

            <div class="form-group">
                <label for="description">{$translator->translate('Logo URL')}</label>
                <input type="text" class="form-control" id="logoUrl" name="logourl" placeholder="{$translator->translate('Logo URL')}" value="{$asset['logourl']}" />
            </div>

            <div class="form-group">
                <label for="description">{$translator->translate('Video URL')}</label>
                <input type="text" class="form-control" id="videoUrl" name="videourl" placeholder="{$translator->translate('Video URL')}" value="{$asset['videourl']}" />
            </div>

            <div class="form-group">
                <label for="description">{$translator->translate('Link URL')}</label>
                <input type="text" class="form-control" id="linkUrl" name="linkurl" placeholder="{$translator->translate('Link URL')}" value="{$asset['linkurl']}" />
            </div>

            <div class="form-group attachment-group">
                    <label for="description">{$translator->translate('Attachments')}</label>
                    <div class="attachments">
{$attachmentHTML}
                    <div class="file-group" data-id="0">
                        <div class="file-upload-remove glyphicon glyphicon-remove"></div>
                        <label><span class="glyphicon glyphicon-plus"></span></label>
                        <input type="file" name="binary[]" class="file-upload-input" />
                    </div>

                </div>
        </div>

        <button type="submit" class="btn btn-primary btn-asset-save">{$translator->translate('Save changes')}</button>
        </form>
    </div>
</div>

</div>

EOT;

include('inc/footer.html');
?>
