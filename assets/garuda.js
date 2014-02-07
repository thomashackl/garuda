STUDIP.Garuda = {

    init: function() {
        if ($('#institute').val() != '') {
            STUDIP.Garuda.getConfig();
        }
    },

    openSelected: function() {
        $('li.degree label').bind('click', function() {
            $(this).parents('li.degree').find('span.actions').toggle();
        });
    
        $('a.all').bind('click', function() {
            $(this).parents('li.degree').find('li.profession input[type="checkbox"]').each(function() { $(this).attr('checked', true); });
        });
    
        $('a.none').bind('click', function() {
            $(this).parents('li.degree').find('li.profession input[type="checkbox"]').each(function() { $(this).attr('checked', false); });
        });

        $('input.subtree:checked').each(function() {
            $('#'+$(this).data('degree-id')).attr('checked', true);
            $('#actions_'+$(this).data('degree-id')).attr('style', 'inline');
        });
    },

    getConfig: function() {
        $('#config').load($('#institute').data('update-url') + '/' + $('#institute').val());
    }

};