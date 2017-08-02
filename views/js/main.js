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

    var highlightMenu = function() {
        var path = window.location.pathname;
        $('a[href="'+path+'"]').parent().addClass('active');
    };

    highlightMenu();

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

    $('.ui-modal-button-catalog').on('click', function(evt){
        var jButton = $(this);
        var target = jButton.data('target');
        var id = jButton.data('id');
        var name = jButton.data('name');
        $('#_name').val(name);
        $('#_id').val(id);
    });

    $('.btn-catalog-save').on('click', function(evt) {

        var jName = $('#_name');
        var jId = $('#_id');
        var jType = $('#_type');

        var name = jName.val();
        var id = jId.val();
        var type = jType.val();

        if(name.length === 0) {
            jName.focus();
            return;
        }

        var jModal = $('.modal-catalog');

        $.ajax({
            url: '/api/v1/catalog', 
            method: 'POST',
            data: {
                name: name, 
                id: id, 
                type: type
            }, 
            success: function(result){
                console.log('### success ###');
                jModal.modal('hide');
                window.location.reload(true);
            }, 
            error: function(error){
                console.log('### error ###');
                console.log(error);
            }
        });
    });
});