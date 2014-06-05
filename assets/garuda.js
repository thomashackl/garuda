STUDIP.Garuda = {

    configInit: function() {
        if ($('#institute').val() != '') {
            this.getConfig();
        }
    },

    configOpenSelected: function() {
        $('li.degree label').click(function(event) {
            $(this).parents('li.degree').find('span.actions').toggle();
        });

        $('li.degree').find('a.all').click(function(event) {
            $(this).parents('li.degree').find('li.subject input[type="checkbox"]').attr('checked', true);
        });

        $('a.none').click(function(event) {
            $(this).parents('li.degree').find('li.subject input[type="checkbox"]').attr('checked', false);
        });

        $('li.degree').find('input.subtree:checked').each(function() {
            $('#'+$(this).data('degree-id')).attr('checked', true);
            $('#actions_'+$(this).data('degree-id')).attr('style', 'inline');
        });

        $('li.faculty label').on('click', function(event) {
            $(this).parents('li.faculty').find('span.actions').toggle();
        });

        $('li.faculty').find('a.all').on('click', function(event) {
            $(this).parents('li.faculty').find('li.institute input[type="checkbox"]').each(function() { $(this).attr('checked', true); });
        });

        $('li.faculty').find('a.none').on('click', function(event) {
            $(this).parents('li.faculty').find('li.institute input[type="checkbox"]').each(function() { $(this).attr('checked', false); });
        });

        $('li.faculty').find('input.faculty_select').on('click', function(event) {
            var father = $(this).parents('li.faculty');
            var inputs = father.find('input.subtree');
            if ($(this).attr('checked')) {
                inputs.attr('disabled', true);
                inputs.attr('checked', true);
            } else {
                inputs.removeAttr('disabled');
                inputs.removeAttr('checked');
            }
        });

        $('li.faculty').find('input.subtree:checked').each(function() {
            $('#'+$(this).data('faculty-id')).attr('checked', true);
            $('#actions_'+$(this).data('faculty-id')).attr('style', 'inline');
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
            } else {
                $('#reclist').css('display', 'none');
                $('#reclist textarea').attr('disabled', true);
            }
        });
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
        $('input[name="message"]').typing({
            stop: function(event, elem) {
                $('#message_preview').load($('input[name="message"]').data('preview-url'), 'text='+$('input[name="message"]').html());
            },
            delay: 500
        });
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
            url = updateUrl[0] + '/' + relation;
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
    }

};