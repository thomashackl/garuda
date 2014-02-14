STUDIP.Garuda = {

    configInit: function() {
        if ($('#institute').val() != '') {
            STUDIP.Garuda.getConfig();
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
        $('input[name="sendto"]').change(function() {
            $('#filters').load($(this).data('update-url'));
        });
    },

    filterInit: function() {
        $('#add_field').click(function(event) {
            event.preventDefault();
            var newField = $('.filterfield').first().clone();
            newField.children('.fieldconfig').empty();
            newField.children('select').val('');
            $('#filterfields').append(newField);
        });
        $('button[name="accept"]').click(function(event) {
            event.preventDefault();
            var targetElement = $('#filter');
            if ($('#filters').children('.nofilter').length > 0) {
                $('#filters').children('.nofilter').remove();
                $('#filters').load($('#filterform').attr('action'));
            } else {
            }
        });
    },

    getFilterConfig: function(element) {
        $(element).siblings('.fieldconfig').load($(element).data('config-url')+'/'+$(element).val());
    }
};