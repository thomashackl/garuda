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

        $('a.all').click(function(event) {
            $(this).parents('li.degree').find('li.profession input[type="checkbox"]').each(function() { $(this).attr('checked', true); });
        });

        $('a.none').click(function(event) {
            $(this).parents('li.degree').find('li.profession input[type="checkbox"]').each(function() { $(this).attr('checked', false); });
        });

        $('input.subtree:checked').each(function() {
            $('#'+$(this).data('degree-id')).attr('checked', true);
            $('#actions_'+$(this).data('degree-id')).attr('style', 'inline');
        });
    },

    getConfig: function() {
        $('#config').load($('#institute').data('update-url') + '/' + $('#institute').val());
    },

    init: function() {
        $('input[name="sendto"]').click(function() {
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
                textfield.load(textfield.data('text-src')+'/sendto_all');
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

    getFieldConfig: function(element) {
        var container = $(element).parent('.fieldconfig');
        var dependent = container.data('depends-on');
        var dependingElement = $('#'+dependent);
        var current = $(element).val();
        var otherCompare = dependingElement.children('select[name="compare_operator[]"]').val();
        var otherValue = dependingElement.children('select[name="value[]"]').val();
        dependingElement.load(container.data('update-url')+'/'+dependent+'/'+current+'/'+otherCompare+'/'+otherValue);
    },

    getFilterConfig: function(element) {
        $(element).siblings('.fieldconfig').load($(element).data('config-url')+'/'+$(element).val());
    }

};