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
        $('#config').load(url);
    },

    init: function() {
        if ($('input[name="sendto"]:checked').val() == 'all') {
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
            if ($('input[name="sendto"]:checked').val() != 'all') {
                $('button[name="add_filter"]').removeClass('hidden-js');
            } else {
                $('button[name="add_filter"]').addClass('hidden-js');
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
                textfield.load(url);
            }
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

    initRecipientView: function() {
        $('li.faculty label').on('click', function(event) {
            var img = $(this).children('img').first();
            var tmp = img.data('toggle-icon');
            img.data('toggle-icon', img.attr('src'));
            img.attr('src', tmp);
        });
    },

    getFieldConfig: function(element) {
        var otherCompare = '';
        var otherValue = '';
        var updateUrl = $(element).parents('.fieldconfig').data('update-url').split('?');
        if ($(element).parents('.filterfield').siblings('.filterfield').length > 0) {
            var r = this.getRestriction($(element).parents('.filterfield').children('option:selected').first());
            if (r != null) {
                url += '/'+r[0]+'/'+r[1];
            }
        }
        url = updateUrl[0];
        if (updateUrl[1] != '') {
            url += '?'+updateUrl[1];
        }
    },

    getFilterConfig: function(element) {
        var otherCompare = '';
        var otherValue = '';
        var textSrc = $(element).data('config-url').split('?');
        var url = textSrc[0]+'/'+encodeURIComponent($(element).val());
        if ($(element).parents('.filterfield').siblings('.filterfield').length > 0) {
            var r = this.getRestriction($(element).children('option:selected').first());
            if (r != null) {
                url += '/'+r[0]+'/'+r[1];
            }
        }
        if (textSrc[1] != '') {
            url += '?'+textSrc[1];
        }
        $(element).siblings('.fieldconfig').load(url);
    },

    getRestriction: function(element) {
        var relation = $(element).data('relation');
        var other = $(element).parents('#filterfields').find('.filterfield').find('select[name="field[]"]');
        for (var i=0 ; i<other.length ; i++) {
            var current = $(other[i]);
            if (current.val() == relation) {
                var compare = current.parents('.filterfield').find('select[name="compare_operator[]"]').val();
                var value = current.parents('.filterfield').find('select[name="value[]"]').val();
                return new Array(compare, value);
            }
        }
        return null;
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