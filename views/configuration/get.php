<?php
use Studip\Button;
?>
<article>
    <section>
        <label class="caption" for="perm">
            <?= dgettext('garudaplugin', 'Welche Rechte werden mindestens an dieser Einrichtung ben�tigt, um den Nachrichtenversand nutzen zu d�rfen?') ?>
        </label>
        <select name="perm" id="perm">
            <option value="admin"<?= ($config['min_perm'] == 'admin') ? ' selected="selected"' : '' ?>><?= dgettext('garudaplugin', 'admin') ?></option>
            <option value="dozent"<?= ($config['min_perm'] == 'dozent') ? ' selected="selected"' : '' ?>><?= dgettext('garudaplugin', 'dozent') ?></option>
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