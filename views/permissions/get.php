<?php
use Studip\Button;
?>
<article>
    <section>
        <label for="perm">
            <?= dgettext('garuda', 'Welche Rechte werden mindestens an dieser Einrichtung benötigt, um den Nachrichtenversand nutzen zu dürfen?') ?>
        </label>
        <select name="perm" id="perm">
            <option value="admin"<?= ($config['min_perm'] == 'admin') ? ' selected' : '' ?>><?= dgettext('garuda', 'admin') ?></option>
            <option value="dozent"<?= ($config['min_perm'] == 'dozent') ? ' selected' : '' ?>><?= dgettext('garuda', 'dozent') ?></option>
            <option value="tutor"<?= ($config['min_perm'] == 'tutor') ? ' selected' : '' ?>><?= dgettext('garuda', 'tutor') ?></option>
        </select>
    </section>
    <section>
        <?= $this->render_partial('permissions/_studycourses') ?>
    </section>
    <section>
        <?= $this->render_partial('permissions/_institutes') ?>
    </section>
    <?= Button::createAccept(dgettext('garuda', 'Einstellungen für die aktuell gewählte Einrichtung speichern'), 'submit') ?>
</article>
<script type="text/javascript">
//<!--
    STUDIP.Garuda.configOpenSelected();
//-->
</script>
