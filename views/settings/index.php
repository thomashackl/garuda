<form class="default" action="<?= $controller->url_for('settings/save') ?>" method="post">
    <header>
        <h1><?= dgettext('garudaplugin', 'Einstellungen für den Nachrichtenversand') ?></h1>
    </header>
    <section>
        <div>
            <?php if ($schedule->hour === null) : ?>
                <?php if ($schedule->minute === null) : ?>
                    <?= dgettext('garudaplugin', 'minütlich') ?>
                <?php elseif ($schedule->minute >= 0) : ?>
                    <?= sprintf(dgettext('garudaplugin',
                        'Anstehende Nachrichten werden stündlich, zur %d. Minute, verschickt.'),
                        $schedule->minute) ?>
                <?php else : ?>
                    <?= sprintf(dgettext('garudaplugin',
                        'Anstehende Nachrichten werden alle %d Minuten verschickt.'),
                        abs($schedule->minute)) ?>
                <?php endif ?>
            <?php elseif ($schedule->hour >= 0) : ?>
                <?= sprintf(dgettext('garudaplugin',
                    'Anstehende Nachrichten werden täglich um %d:%d Uhr verschickt.'),
                    $schedule->hour, $schedule->minute) ?>
            <?php else : ?>
                <?= sprintf(dgettext('garudaplugin',
                    'Anstehende Nachrichten werden alle %d Stunden, zur %d. Minute, verschickt.'),
                    abs($schedule->hour), $schedule->minute) ?>
            <?php endif ?>
            <?= sprintf(dgettext('garudaplugin', 'Nächste Ausführung: %s'),
                date('d.m.Y H:i', $schedule->next_execution)) ?>
        </div>
        <?= Studip\LinkButton::create(dgettext('garudaplugin', 'Cronjob-Einstellungen bearbeiten'),
            URLHelper::getURL('dispatch.php/admin/cronjobs/schedules/edit/' . $schedule->id . '/1')) ?>
    </section>
    <section>
        <label>
            <?= dgettext('garudaplugin','Wie oft (in Tagen) sollen bereits komplett abgeschlossene '.
                'Nachrichtenversandaufträge gelöscht werden?') ?>
            <input type="number" name="cleanup" value="<?= $cleanup ?>">
        </label>
    </section>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('garudaplugin', 'Speichern'), 'save') ?>
        <?= Studip\LinkButton::createCancel(dgettext('garudaplugin', 'Abbrechen'),
            $controller->url_for('settings')) ?>
    </footer>
</form>
