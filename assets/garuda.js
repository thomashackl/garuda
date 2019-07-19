(function ($, STUDIP) {
    'use strict';

    STUDIP.Garuda = {

        configInit: function() {
            if ($('#institute').val() != '') {
                this.getConfig();
            }
        },

        configOpenSelected: function() {
            $('input[type="checkbox"].selector:checked').parents('li').children('input[type="checkbox"].tree').attr('checked', true);
            $('span.actions a.all').each(function() {
                $(this).on('click', function() {
                    $(this).parent().siblings('ul').children('li').children('input[type="checkbox"].selector').attr('checked', true);
                });
            });
            $('span.actions a.none').each(function() {
                $(this).on('click', function() {
                    $(this).parent().siblings('ul').children('li').children('input[type="checkbox"].selector').attr('checked', false);
                });
            });
        },

        getConfig: function() {
            var textSrc = $('#institute').data('update-url').split('?');
            var url = textSrc[0]+'/'+encodeURIComponent($('#institute').val());
            if (textSrc[1] != '') {
                url += '?'+textSrc[1];
            }
            $('#config').html($('<img>').attr('src', STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'));
            $('#config').load(url);
        },

        uploadFromInput: function (input, type) {
            STUDIP.Garuda.uploadFiles(input.files, type);
            jQuery(input).val('');
        },

        fileIDQueue: 1,

        uploadFiles: function (files, type) {
            for (var i = 0; i < files.length; i++) {
                var fd = new FormData();
                fd.append('file', files[i], files[i].name);
                var statusbar = $('#' + type + '-statusbar-container .statusbar').first().clone().show();
                statusbar.appendTo('#' + type + '-statusbar-container');
                fd.append('message_id', $('#provisional-id').val());
                STUDIP.Garuda.uploadFile(fd, statusbar, type);
            }
        },

        uploadFile: function (formdata, statusbar, type) {
            $.ajax({
                xhr: function() {
                    var xhrobj = $.ajaxSettings.xhr();
                    if (xhrobj.upload) {
                        xhrobj.upload.addEventListener('progress', function(event) {
                            var percent = 0;
                            var position = event.loaded || event.position;
                            var total = event.total;
                            if (event.lengthComputable) {
                                percent = Math.ceil(position / total * 100);
                            }
                            //Set progress
                            statusbar.find('.progress')
                                .css({'min-width': percent + '%', 'max-width': percent + '%'});
                            statusbar.find('.progresstext')
                                .text(percent === 100 ? $('#' + type + '-upload-finished').text() : percent + '%');
                        }, false);
                    }
                    return xhrobj;
                },
                url: STUDIP.ABSOLUTE_URI_STUDIP + 'plugins.php/garudaplugin/message/upload/' + type,
                type: 'POST',
                contentType: false,
                processData: false,
                cache: false,
                data: formdata,
                dataType: 'json'
            }).done(function(data) {
                statusbar.find('.progress').css({'min-width': '100%', 'max-width': '100%'});
                var file = $('#' + type + ' .files > .file').first().clone();
                file.on('click', function() { STUDIP.Garuda.removeFile(file); });
                file.find('.name').text(data.name);
                if (data.size < 1024) {
                    file.find('.size').text(data.size + 'B');
                }
                if (data.size > 1024 && data.size < 1024 * 1024) {
                    file.find('.size').text(Math.floor(data.size / 1024) + 'KB');
                }
                if (data.size > 1024 * 1024 && data.size < 1024 * 1024 * 1024) {
                    file.find('.size').text(Math.floor(data.size / 1024 / 1024) + 'MB');
                }
                if (data.size > 1024 * 1024 * 1024) {
                    file.find('.size').text(Math.floor(data.size / 1024 / 1024 / 1024) + 'GB');
                }
                file.find('.icon').html(data.icon);
                file.data('document-id', data.document_id);
                file.appendTo('#' + type + ' .files');
                file.fadeIn(300);
                statusbar.find('.progresstext').text($('#' + type + '-upload-received-data').text());
                statusbar.delay(1000).fadeOut(300, function () { $(this).remove(); });
            }).fail(function(jqxhr, status, errorThrown) {
                var error = jqxhr.responseJSON.error;

                statusbar.find('.progress').addClass('progress-error').attr('title', error);
                statusbar.find('.progresstext').html(error);
                statusbar.on('click', function() { $(this).fadeOut(300, function () { $(this).remove(); })});
            });
        },

        removeFile: function(target) {
            $.ajax({
                url: STUDIP.ABSOLUTE_URI_STUDIP + 'plugins.php/garudaplugin/message/delete_file',
                data: {
                    'document_id' : target.closest('li').data('document-id'),
                    'message_id' : target.closest('form').find('input[name=message_id]').val()
                },
                type: 'POST'
            });
            target.closest("li").fadeOut(300, function() { target.remove(); });
        },

        init: function() {
            if ($('input[name="sendto"]:checked').val() == 'all' ||
                    $('input[name="sendto"]:checked').val() == 'courses' ||
                    $('input[name="sendto"]:checked').val() == 'list') {
                $('button[name="add_filter"]')
                    .addClass('hidden-js')
                    .attr('disabled', true);
            }

            $('input[name="sendto"]').on('click', function() {
                var textSrc = $('.filtertext').data('text-src').split('?');
                if ($('input[name="sendto"]:checked').val() == 'courses') {
                    var url = textSrc[0] + '/sendto_courses';
                } else {
                    var url = textSrc[0] + '/sendto_all';
                }
                if (textSrc[1] != '') {
                    url += '?' + textSrc[1];
                }
                $('.filtertext').load(url);
                $('.userfilter').remove();
                if ($('input[name="sendto"]:checked').val() != 'all' &&
                    $('input[name="sendto"]:checked').val() != 'courses' &&
                        $('input[name="sendto"]:checked').val() != 'list') {
                    $('button[name="add_filter"]')
                        .removeClass('hidden-js')
                        .attr('disabled', false);
                } else {
                    $('button[name="add_filter"]')
                        .addClass('hidden-js')
                        .attr('disabled', true);
                }
                if ($('input[name="sendto"]:checked').val() == 'list') {
                    $('#reclist').css('display', 'block');
                    $('#reclist textarea').attr('disabled', false);
                    $('span.filtertext').addClass('hidden-js');
                } else {
                    $('#reclist').css('display', 'none');
                    $('#reclist textarea').attr('disabled', true);
                    $('span.filtertext').removeClass('hidden-js');
                }
                if ($('input[name="sendto"]:checked').val() == 'courses') {
                    $('div#garuda-coursesearch').removeClass('hidden-js');
                } else {
                    $('div#garuda-coursesearch').addClass('hidden-js');
                }
            });

            $('input[name="exclude"]').on('click', function() {
                if ($(this).prop('checked')) {
                    $('#excludelist').css('display', 'block');
                    $('textarea[name="excludelist"]').attr('disabled', false);
                } else {
                    $('#excludelist').css('display', 'none');
                    $('textarea[name="excludelist"]').attr('disabled', true);
                }
            });

            $('input[name="use_tokens"]').on('click', function(event) {
                $('section.use-tokens').toggleClass('hidden-js');
                $('#tokens li.file:not(:first)').each(function() {
                    STUDIP.Garuda.removeFile($(this));
                });
            });

            $('a.remove-file').on('click', function(e) {
                STUDIP.Garuda.removeFile($(e.target));
            });

            var markers = $('#garuda-markers');
            var addMarker = $('#garuda-add-marker');
            markers.children('select').on('change', function() {
                var selected = $(this).children('option:selected');
                $('#garuda-marker-description').html(selected.data('description'));
                if (selected.attr('value') != '') {
                    addMarker.removeClass('hidden-js');
                } else {
                    addMarker.addClass('hidden-js');
                }
            });

            // WYSIWYG enabled -> move markers field below editor toolbar.
            // (some day perhaps a button for serial mail fields can be added to CKEditor here).
            var editorId = $('textarea[name="message"]').attr('id');
            if (STUDIP.wysiwyg_enabled && CKEDITOR.instances[editorId] != null) {
                var id = $('textarea[name="message"]').attr('id');
                CKEDITOR.instances[editorId].on('instanceReady', function() {
                    markers.insertAfter($('div.cktoolbar'));
                });

                addMarker.unbind().on('click', function() {
                    CKEDITOR.instances[editorId].insertText($('#garuda-markers select option:selected').attr('value'));
                    return false;
                });

            // No WYSIWYG -> normal toolbar.
            } else {
                markers.addClass('no-wysiwyg');
                markers.insertAfter('div.buttons');
                addMarker.on('click', function() {
                    markers.parent().children('textarea').
                    insertAtCaret($('#garuda-markers select option:selected').attr('value'));
                    return false;
                });
            }

            $('input[name="send_at_date"]').on('click', function(event) {
                $('section.send_date').toggleClass('hidden-js');
            });

            $('input[name="send_date"]').datetimepicker();

            $('.userfilter_actions a.delete').on('click', function(event) {
                event.preventDefault();
                var father = $(this).parents('.userfilter');
                var container = father.parent();
                father.remove();
                if (container.children('.userfilter').length == 0) {
                    var textfield = container.children('.filtertext');
                    var textSrc = textfield.data('text-src').split('?');
                    var url = textSrc[0]+'/sendto_all';
                    if (textSrc[1] != '') {
                        url += '?'+textSrc[1];
                    }
                    textfield.html($('<img>').attr('src', STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'));
                    textfield.load(url);
                }
            });

            if ($('input[name="sendto"]:checked').val() != 'list') {
                $('#reclist').css('display', 'none');
                $('#reclist textarea').attr('disabled', true);
            }


            if (!$('input[name="exclude"]').attr('checked')) {
                $('#excludelist').css('display', 'none');
                $('textarea[name="excludelist]').attr('disabled', true);
            }

            // Use jQuery typing plugin for message preview.
            if (!STUDIP.wysiwyg_enabled || CKEDITOR.instances.length == 0) {
                $('textarea[name="message"]').typing({
                    stop: function () {
                        var url = $('textarea[name="message"]').data('preview-url').split('?');
                        url = url[0];
                        $('#message_preview_text').load(url, {
                            'text': encodeURIComponent($('textarea[name="message"]').val())
                        });
                    },
                    delay: 500
                });
                var width = $('textarea[name="message"]').width();
                $('#message_preview_text').width(width);
                $('#message_preview_text').css('max-width', width);
                var height = $('textarea[name="message"]').height();
                //$('#message_preview_text').height(height);
                $('#message_preview').css('left', width + 30);
            }
        },

        initFilter: function() {
            $('#add_field').on('click', function(event) {
                event.preventDefault();
                var newField = $('.filterfield').first().clone();
                newField.children('.fieldconfig').empty();
                newField.children('select').val('');
                $('#filterfields').append(newField);
            });
        },

        initField: function() {
            $('select[name="value[]"] option').each(function() {
                var prev = $(this).prev();
                if ($(this).attr('value').indexOf('_children') != -1) {
                    prev.attr('class', 'fac');
                    $(this).attr('class', 'fac_all');
                }
                if (prev.attr('class') == 'fac_all' || prev.attr('class') == 'inst') {
                    $(this).attr('class', 'inst');
                }
            });
        },

        initRecipientView: function() {
            $('li.degree label').on('click', function(event) {
                var img = $(this).children('img').first();
                var tmp = img.data('toggle-icon');
                img.data('toggle-icon', img.attr('src'));
                img.attr('src', tmp);
            });
            $('li.faculty label').on('click', function(event) {
                var img = $(this).children('img').first();
                var tmp = img.data('toggle-icon');
                img.data('toggle-icon', img.attr('src'));
                img.attr('src', tmp);
            });
        },

        getFieldConfig: function(element) {
            var ancestor = $(element).parents('.filterfield');
            var relation = ancestor.data('relation');
            if (relation != '') {
                var compare = encodeURIComponent($(element).siblings('select[name="compare_operator[]"]').val());
                var value = encodeURIComponent($(element).val());
                var relatedElement = $('#'+relation);
                var updateUrl = $(element).parent().data('update-url').split('?');
                var url = updateUrl[0] + '/' + relation;
                var otherCompare = relatedElement.find('select[name="compare_operator[]"]').val();
                var otherValue = relatedElement.find('select[name="value[]"]').val();
                url += '/'+compare+'/'+value;
                if (updateUrl[1] != '') {
                    url += '?'+updateUrl[1];
                }
                relatedElement.children('.fieldconfig').html($('<img>').attr('src', STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'));
                relatedElement.children('.fieldconfig').load(url, function() {
                    relatedElement.children('.fieldconfig').find('option[value="'+otherCompare+'"]').attr('selected', true);
                    relatedElement.children('.fieldconfig').find('option[value="'+otherValue+'"]').attr('selected', true);
                });
            }
        },

        getFilterConfig: function(element) {
            var otherCompare = '';
            var otherValue = '';
            var textSrc = $(element).data('config-url').split('?');
            var url = textSrc[0]+'/'+encodeURIComponent($(element).val());
            if (textSrc[1] != '') {
                url += '?'+textSrc[1];
            }
            $(element).siblings('.fieldconfig').html($('<img>').attr('src', STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'));
            $(element).siblings('.fieldconfig').load(url);
        },

        removeFilter: function(element) {
            $(element).parents('.userfilter').remove();
            var textfield = $('.filtertext');
            var filters = $('.userfilter').length;
            var textSrc = textfield.data('text-src').split('?');
            if (filters == 0) {
                var url = textSrc[0]+'/sendto_all';
                if (textSrc[1] != '') {
                    url += '?' + textSrc[1];
                }
            } else if (filters == 1) {
                var url = textSrc[0] + '/sendto_filtered/true';
                if (textSrc[1] != '') {
                    url += '?' + textSrc[1];
                }
            } else {
                var url = textSrc[0] + '/sendto_filtered' + textSrc[1];
                if (textSrc[1] != '') {
                    url += '?' + textSrc[1];
                }
            }
            textfield.html($('<img>').attr('src', STUDIP.ASSETS_URL + 'images/ajax_indicator_small.gif'));
            textfield.load(url);
            return false;
        },

        replaceSender: function(id, name) {
            var cleaned = $('<div>').html(name).text();
            $('input#garuda-senderid').attr('value', id);
            $('span#garuda-sendername').html('(' + cleaned + ')').removeClass('hidden-js');
            return false;
        },

        addCourse: function(id, name) {
            // Remove all formatting from name.
            var cleaned = $('<div>').html(name).text();
            var list = $('ul#garuda-courses');
            if (list.children('li.' + id).length == 0) {
                var child = $('<li>').
                    addClass(id).
                    html(cleaned);
                var input = $('<input>').
                    attr('type', 'hidden').
                    attr('name', 'courses[]').
                    attr('value', id);
                child.append(input);
                list.append(child);
            }
        },

        addCC: function(id, name) {
            // Remove all formatting from name.
            var cleaned = $('<div>').html(name).text();
            var list = $('ul#garuda-cc');
            if (list.children('li.' + id).length == 0) {
                var child = $('<li>').
                    addClass(id).
                    html(cleaned);
                var input = $('<input>').
                    attr('type', 'hidden').
                    attr('name', 'cc[]').
                    attr('value', id);
                child.append(input);
                list.append(child);
            }
        }

    };

    $(document).ready(function () {
        $('span.garuda-more a, span.garuda-messagetext a').on('click', function(event) {
            $(event.target).closest('span').toggleClass('hidden-js');
            $(event.target).closest('span').siblings('span').toggleClass('hidden-js');
            return false;
        });
        $('input[type="radio"].garuda-sender-config').on('click', function(event) {
            if ($(this).attr('value') == 'person') {
                $('#garuda-sender-choose-person').removeClass('hidden-js');
            } else {
                $('#garuda-sender-choose-person').addClass('hidden-js');
                $('span#garuda-sendername').addClass('hidden-js');
            }
        });
        if ($('form.garuda-js-init').length > 0) {
            STUDIP.Garuda.init();
        }
        if ($('fieldset[name="database"]').length > 0) {
            $('input[name="enable"]').on('click', function(event) {
                if ($('input[name="enable"]').is(':checked')) {
                    $('fieldset[name="database"]').removeClass('hidden-js');
                    $('fieldset[name="tableinfo"]').removeClass('hidden-js');
                    if ($('select[name="dbtype"]').children('option:selected').val() == 'informix') {
                        $('fieldset[name="additional"]').removeClass('hidden-js');
                    }
                } else {
                    $('fieldset[name="database"]').addClass('hidden-js');
                    $('fieldset[name="tableinfo"]').addClass('hidden-js');
                    $('fieldset[name="additional"]').addClass('hidden-js');
                }
            });
            $('select[name="dbtype"]').on('change', function(event) {
                if ($(this).children('option:selected').val() == 'informix') {
                    $('fieldset[name="additional"]').removeClass('hidden-js');
                } else {
                    $('fieldset[name="additional"]').addClass('hidden-js');
                }
            });
        }
    });
    $(document).on('dialog-update', function(event) {
        if ($('form.garuda-js-init').length > 0) {
            STUDIP.Garuda.init();
        }
    });

}(jQuery, STUDIP));
