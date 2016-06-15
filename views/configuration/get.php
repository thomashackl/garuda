<?php
use Studip\Button;
?>
<article>
    <section>
        <label class="caption" for="perm">
            <?= dgettext('garudaplugin', 'Welche Rechte werden mindestens an dieser Einrichtung ben�tigt, um den Nachrichtenversand nutzen zu d�rfen?') ?>
        </label>
        <select name="perm" id="perm">
            <option value="admin"<?= ($config['min_perm'] == 'admin') ? ' selected' : '' ?>><?= dgettext('garudaplugin', 'admin') ?></option>
            <option value="dozent"<?= ($config['min_perm'] == 'dozent') ? ' selected' : '' ?>><?= dgettext('garudaplugin', 'dozent') ?></option>
            <option value="tutor"<?= ($config['min_perm'] == 'tutor') ? ' selected' : '' ?>><?= dgettext('garudaplugin', 'tutor') ?></option>
        </select>
    </section>
    <section>
        <?= $this->render_partial('configuration/_studycourses') ?>
    </section>
    <section>
        <?= $this->render_partial('configuration/_institutes') ?>
    </section>
    <?= Button::createAccept(dgettext('garudaplugin', 'Einstellungen f�r die aktuell gew�hlte Einrichtung speichern'), 'submit') ?>
</article>
<script type="text/javascript">
//<!--
    STUDIP.Garuda.configOpenSelected();
//-->
</script>
