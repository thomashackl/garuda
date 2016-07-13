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

        init: function() {
            if ($('input[name="sendto"]:checked').val() == 'all' ||
                    $('input[name="sendto"]:checked').val() == 'list') {
                $('button[name="add_filter"]').addClass('hidden-js');
            }

            $('input[name="sendto"]').on('click', function() {
                var textSrc = $('.filtertext').data('text-src').split('?');
                var url = textSrc[0]+'/sendto_all';
                if (textSrc[1] != '') {
                    url += '?'+textSrc[1];
                }
                $('.filtertext').load(url);
                $('.userfilter').remove();
                if ($('input[name="sendto"]:checked').val() != 'all' &&
                        $('input[name="sendto"]:checked').val() != 'list') {
                    $('button[name="add_filter"]').removeClass('hidden-js');
                } else {
                    $('button[name="add_filter"]').addClass('hidden-js');
                }
                if ($('input[name="sendto"]:checked').val() == 'list') {
                    $('#reclist').css('display', '');
                    $('#reclist textarea').attr('disabled', false);
                    $('span.filtertext').addClass('hidden-js');
                } else {
                    $('#reclist').css('display', 'none');
                    $('#reclist textarea').attr('disabled', true);
                    $('span.filtertext').removeClass('hidden-js');
                }
            });

            $('input[name="use_tokens"]').on('click', function(event) {
                $('section.use_tokens').toggleClass('hidden-js');
            });

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

            $('#reclist').css('display', 'none');
            $('#reclist textarea').attr('disabled', true);

            // Use jQuery typing plugin for message preview.
            $('textarea[name="message"]').typing({
                stop: function() {
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
            $('#message_preview').css('left', width+30);
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
                    url += '?'+textSrc[1];
                }
            } else if (filters == 1) {
                var url = textSrc[0]+'/sendto_filtered/true';
                if (textSrc[1] != '') {
                    url += '?'+textSrc[1];
                }
            } else {
                var url = textSrc[0]+'/sendto_filtered'+textSrc[1];
                if (textSrc[1] != '') {
                    url += '?'+textSrc[1];
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
    });
    $(document).on('dialog-open', function(event) {
        if ($('form.garuda-js-init').length > 0) {
            STUDIP.Garuda.init();
        }
    });

}(jQuery, STUDIP));
