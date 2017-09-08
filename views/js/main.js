$(function(){
    var methods = {
        init: function() {
            // Global
            // Navigation bars
            var path = window.location.pathname;
            $('a[href="'+path+'"]').parent().addClass('active');

            // Checkbox styles
            $('.form-group').on('change', 'input[type="checkbox"]', function(evt) {
                var label = $(this).parent();
                var isChecked = $(this).prop('checked');
                if(isChecked) {
                    label.addClass('ui-button-block-selected');
                }
                else {
                    label.removeClass('ui-button-block-selected');
                }
            });
            var wrapper = $('#t-wrapper');
            var jclass = wrapper.attr('class');
            var method = jclass.split(' ');
            for(var k in method){
                if(typeof(methods[method[k]]) === 'function') methods[method[k]].apply();
            }
            if(typeof(methods[method[0]]) === 'object' && typeof(methods[method[0]]['init']) === 'function') methods[method[0]]['init'].apply();
            if(typeof(methods[method[0]]) === 'object' && typeof(methods[method[0]][method[1]]) === 'function') methods[method[0]][method[1]].apply();
        }, 
        home: function() {
            console.log('home');
        }, 
        asset: function() {
            console.log('asset');
            // Remove the image input from form
            // Asset
            $('.form-group').on('click', '.file-upload-remove', function(evt) {

                var item = $(this).parent();
                var id = item.data('id');

                if(id == '0') {
                    item.remove();
                    return;
                }

                $.ajax({
                    url: '/api/v1/assets/attachment/' + id, 
                    method: 'DELETE', 
                    success: function() {
                        item.remove();
                    }, 
                    error: function() {

                    }
                });

            });

            // UI File upload preparation
            // Asset
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
        }, 
        event: {
            init: function() {
                console.log('event');
            }, 
            details: function() {
                // events
                var jCompany = $('.ui-event #company');
                var jId = $('.ui-event #id');
                var jForm = $('.ui-event form');
                var areVisitorsLoaded = false;
                jForm.on('submit', function(evt) {
                    return areVisitorsLoaded;
                });

                var checkTimeline = function() {
                    var jTimelines = $('.form-group-timeline .timeline-container .form-group-container');
                    var jEmpty = $('.form-group-timeline .timeline-container .form-group-no-data');
                    
                    if(jTimelines.length === 0) {
                        jEmpty.show();
                    }
                    else {
                        jEmpty.hide();
                    }
                };

                $('.ui-event').on('click', '.timeline-remove', function(evt) {
                    var id = $(this).data('id');
                    var jContainer = $(this).closest('.form-group-container');

                    if(id == '0') {
                        jContainer.remove();
                        checkTimeline();
                        return;
                    }
                    $.ajax({
                        url: '/api/v1/timelines/' + id, 
                        method: 'DELETE', 
                        success: function(response) {
                            jContainer.remove();
                            checkTimeline();
                        }
                    });
                    
                });
                checkTimeline();

                $('.ui-event .timeline-add').on('click', function(evt) {
                    var count = $('.form-group-timeline .timeline-container .form-group-container').length;

                    var html = $('.ui-template-timeline').html();
                    var jTimeline = $(html);
                    var jContainer = $('.ui-event .timeline-container');

                    var jControls = jTimeline.find('.form-control');

                    jControls.each(function(i) {
                        var name = $(this).data('name');
                        console.log(name);
                        $(this).prop('name', name+'['+count+']');
                    });

                    jContainer.find('.form-group-no-data').hide();
                    jContainer.append(jTimeline);
                });
                //
                var loadVisitors = function(evt) {
                    var idcompany = jCompany.val();
                    var id = jId.val();

                    $.ajax({
                        url: '/api/v1/visitors/company/' + idcompany + '/event/' + id + '', 
                        method: 'GET', 
                        success: function(response) {
                            var jGroup = $('.form-group-visitors');
                            var jLabel = $('<label for="_company">Visitors</label>');
                            var jContainer = $('<div class="form-control form-control-auto-height" id="_company"></div>');
                            //
                            var all = response.all;
                            var selected = response.selected;

                            var html = '';
                            var company = all.length > 0 ? all[0].company : '';
                            var subtitle = $('<label class="ui-label-block"></label>').text(company);
                            jContainer.append(subtitle);
                            for(var i in all) {
                                var jBlock = $('<label class="ui-button-block"></label>');
                                var jBox = $('<input type="checkbox" name="idvisitor[]" value="'+all[i].id+'" />');

                                if(all[i].company != company) {
                                    company = all[i].company;
                                    jContainer.append('<div class="clear"></div>');
                                    var subtitle = $('<label class="ui-label-block"></label>').text(company);
                                    jContainer.append(subtitle);
                                }

                                selected.findIndex(function(x) { 
                                    if(x.idvisitor == all[i].id) {
                                        jBox.attr('checked', true);

                                        jBlock.addClass('ui-button-block-selected');
                                    }
                                });

                                jBlock.append(jBox);

                                jBlock.append(all[i].firstname + ', ' + all[i].lastname);

                                jContainer.append(jBlock);
                                
                            }
                            
                            //
                            jGroup.empty()
                                .append(jLabel)
                                .append(jContainer);

                            areVisitorsLoaded = true;
                        }, 
                        error: function(error) {
                            areVisitorsLoaded = true;
                        }
                    })
                };
                jCompany.on('change', loadVisitors);
                loadVisitors();

                // datepicker
                var datePicker = $('.datepicker').datepicker({format: 'yyyy-mm-dd'}).on('changeDate', function(evt) {
                    datePicker.hide();
                }).data('datepicker');
            }
        }, 
        company: function() {
            console.log('company');

            // Company
            $('.form-group').on('change', '.company-logo-input', function(evt) {

                var group = $(this).parent();
                var label = group.find('label');
                var files = $(this).get(0).files;

                if(files.length > 0) {
                    var reader = new FileReader();
                    var file = files[0];

                    $(reader).on("load", function(evt) {
                        group.addClass('ready').css('background-image', 'url('+evt.target.result+')');
                        label.text('');
                    });
                    reader.readAsDataURL(file);
                }
                else {

                }
            });
            // Remove the image input from form
            // Company
            $('.form-group').on('click', '.company-logo-remove', function(evt) {

                var group = $(this).parent();
                var label = group.find('label');
                var id = group.data('id');
                var jFile = $('.company-logo-input');

                if(id == '0') {
                    jFile.val('');
                    group.removeClass('ready').css('background-image', 'inherit');
                    label.html('<span class="glyphicon glyphicon-plus"></span>');
                    return;
                }

                $.ajax({
                    url: '/api/v1/companies/logo/' + id, 
                    method: 'DELETE', 
                    success: function() {
                        jFile.val('');
                        group.removeClass('ready').css('background-image', 'inherit');
                        label.html('<span class="glyphicon glyphicon-plus"></span>');
                    }, 
                    error: function() {

                    }
                });
            });

        }, 
        visitor: function() {
            console.log('visitor');
            // Company
            $('.form-group').on('change', '.visitor-avatar-input', function(evt) {

                var group = $(this).parent();
                var label = group.find('label');
                var files = $(this).get(0).files;

                if(files.length > 0) {
                    var reader = new FileReader();
                    var file = files[0];

                    $(reader).on("load", function(evt) {
                        group.addClass('ready').css('background-image', 'url('+evt.target.result+')');
                        label.text('');
                    });
                    reader.readAsDataURL(file);
                }
                else {

                }
            });
            // Remove the image input from form
            // Company
            $('.form-group').on('click', '.visitor-avatar-remove', function(evt) {

                var group = $(this).parent();
                var label = group.find('label');
                var id = group.data('id');
                var jFile = $('.visitor-avatar-input');

                if(id == '0') {
                    jFile.val('');
                    group.removeClass('ready').css('background-image', 'inherit');
                    label.html('<span class="glyphicon glyphicon-plus"></span>');
                    return;
                }

                $.ajax({
                    url: '/api/v1/visitors/avatar/' + id, 
                    method: 'DELETE', 
                    success: function() {
                        jFile.val('');
                        group.removeClass('ready').css('background-image', 'inherit');
                        label.html('<span class="glyphicon glyphicon-plus"></span>');
                    }, 
                    error: function() {

                    }
                });
            });
        }, 
        catalog: function() {
            console.log('catalog');
            // Catalog
            $('.ui-modal-button-catalog').on('click', function(evt){
                var jButton = $(this);
                var target = jButton.data('target');
                var id = jButton.data('id');
                var name = jButton.data('name');
                $('#_name').val(name);
                $('#_id').val(id);
            });

            // Catalog
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

        }, 
        download: function() {
            $('.btn-download').on('click', function(evt) {
                var host = $('.bundle-host').val();
                var id = $('.bundle-id').val();
                var url = 'itms-services://?action=download-manifest&amp;url=https://'+host+'/api/v1/download/meta?id=' + id;
                $(window).location.href = url;
            });
        }
    };
    methods.init();


});