<?php if ($and) : ?>
    <?= dgettext('garudaplugin', 'Die Personen, die an allen der oben '.
        'gew�hlten Veranstaltungen teilnehmen, erhalten diese Nachricht.') ?>
<?php else : ?>
    <?= dgettext('garudaplugin', 'Alle Personen, die an einer der oben '.
        'gew�hlten Veranstaltungen teilnehmen, erhalten diese Nachricht.') ?>
<?php endif ?>
