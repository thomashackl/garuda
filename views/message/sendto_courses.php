<?php if ($and) : ?>
    <?= dgettext('garudaplugin', 'Die Personen, die an allen der oben '.
        'gewählten Veranstaltungen teilnehmen, erhalten diese Nachricht.') ?>
<?php else : ?>
    <?= dgettext('garudaplugin', 'Alle Personen, die an einer der oben '.
        'gewählten Veranstaltungen teilnehmen, erhalten diese Nachricht.') ?>
<?php endif ?>
