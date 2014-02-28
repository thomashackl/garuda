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
            $(this).parents('li.degree').find('li.subject input[type="checkbox"]').each(function() { $(this).attr('checked', true); });
        });

        $('a.none').click(function(event) {
            $(this).parents('li.degree').find('li.subject input[type="checkbox"]').each(function() { $(this).attr('checked', false); });
        });

        $('li.degree').find('input.subtree:checked').each(function() {
            $('#'+$(this).data('degree-id')).attr('checked', true);
            $('#actions_'+$(this).data('degree-id')).attr('style', 'inline');
        });

        $('li.faculty label').click(function(event) {
            $(this).parents('li.faculty').find('span.actions').toggle();
        });

        $('li.faculty').find('a.all').click(function(event) {
            $(this).parents('li.faculty').find('li.institute input[type="checkbox"]').each(function() { $(this).attr('checked', true); });
        });

        $('li.faculty').find('a.none').click(function(event) {
            $(this).parents('li.faculty').find('li.institute input[type="checkbox"]').each(function() { $(this).attr('checked', false); });
        });

        $('li.faculty').find('input.faculty_select').click(function(event) {
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
        $('#config').load($('#institute').data('update-url') + '/' + encodeURIComponent($('#institute').val()));
    },

    init: function() {
        if ($('input[name="sendto"]:checked').val() == 'all') {
            $('button[name="add_filter"]').addClass('hidden-js');
        }
        $('input[name="sendto"]').click(function() {
            var textSrc = $('.filtertext').data('text-src').split('?');
            var url = textSrc[0]+'/sendto_all';
            if (textSrc[1] != '') {
                url += '?'+textSrc[1];
            }
            $('.filtertext').load(url);
            $('.userfilter').remove();
            if ($('input[name="sendto"]:checked').val() != 'all') {
                $('button[name="add_filter"]').removeClass('hidden-js');
            } else {
                $('button[name="add_filter"]').addClass('hidden-js');
            }
        });
        $('.userfilter_actions a.delete').click(function(event) {
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
                textfield.load(url);
            }
        });
    },

    initFilter: function() {
        $('#add_field').click(function(event) {
            event.preventDefault();
            var newField = $('.filterfield').first().clone();
            newField.children('.fieldconfig').empty();
            newField.children('select').val('');
            $('#filterfields').append(newField);
        });
    },

    initRecipientView: function() {
        $('li.faculty label').click(function(event) {
            var img = $(this).children('img').first();
            var tmp = img.data('toggle-icon');
            img.data('toggle-icon', img.attr('src'));
            img.attr('src', tmp);
        });
    },

    getFieldConfig: function(element) {
        var container = $(element).parent('.fieldconfig');
        var dependent = container.data('depends-on');
        var dependingElement = $('#'+dependent);
        var current = encodeURIComponent($(element).val());
        var otherCompare = encodeURIComponent(dependingElement.children('select[name="compare_operator[]"]').val());
        var otherValue = encodeURIComponent(dependingElement.children('select[name="value[]"]').val());
        var updateUrl = container.data('update-url').split('?');
        var url = updateUrl[0]+'/'+encodeURIComponent(dependent)+'/'+current+'/'+otherCompare+'/'+otherValue;
        if (updateUrl[1] != '') {
            url += '?'+updateUrl[1];
        }
        dependingElement.load(url);
    },

    getFilterConfig: function(element) {
        $(element).siblings('.fieldconfig').load($(element).data('config-url')+'/'+encodeURIComponent($(element).val()));
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
        textfield.load(url);
        return false;
    }


};