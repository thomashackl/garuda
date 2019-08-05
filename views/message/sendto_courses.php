<?php if ($and) : ?>
    <?= dgettext('garuda', 'Die Personen, die an allen der oben '.
        'gewählten Veranstaltungen teilnehmen, erhalten diese Nachricht.') ?>
<?php else : ?>
    <?= dgettext('garuda', 'Alle Personen, die an einer der oben '.
        'gewählten Veranstaltungen teilnehmen, erhalten diese Nachricht.') ?>
<?php endif ?>
