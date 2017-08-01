$(function(){
    // css only
    $('input[type="checkbox"]').on('change', function(evt) {
        var label = $(this).parent();
        var isChecked = $(this).prop('checked');
        if(isChecked) {
            label.addClass('ui-button-block-selected');
        }
        else {
            label.removeClass('ui-button-block-selected');
        }
    });

    // Remove the image input from form
    $('.form-group').on('click', '.file-upload-remove', function(evt) {

        var item = $(this).parent();
        $.ajax({
            url: '/api/v1/assets/attachment/' + item.data('id'), 
            method: 'DELETE', 
            success: function() {
                item.remove();
            }, 
            error: function() {

            }
        });

    });

    // UI File upload preparation
    $('.form-group').on('change', '.file-upload-input', function(evt) {

        var group = $(this).parent();
        var label = group.find('label');
        var files = $(this).get(0).files;

        if(files.length > 0) {
            var reader = new FileReader();
            var file = files[0];
            label.text(file.name);
            var html = '<div class="file-group" data-id="0"><div class="file-upload-remove glyphicon glyphicon-remove"></div><label><span class="glyphicon glyphicon-plus"></span></label><input type="file" name="binary[]" class="file-upload-input" /></div>'
            group.addClass('ready').after(html);
            $(reader).on("load", function(evt) {
                group.css('background-image', 'url('+evt.target.result+')');
                label.text('');
            });
            reader.readAsDataURL(file);
        }
        else {
            var fCount = $('.form-group.ready').length;

            if(fCount == 1) {
                group.removeClass('ready');
                label.html('<span class="glyphicon glyphicon-plus"></span>');
            }
            else {
                group.remove();
            }
        }
    });
});