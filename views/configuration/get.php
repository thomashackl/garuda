<?php
use Studip\Button;
?>
    <label class="caption" for="perm">
        <?= dgettext('garudaplugin', 'Welche Rechte werden mindestens an dieser Einrichtung ben�tigt, um den Nachrichtenversand nutzen zu d�rfen?') ?>
    </label>
    <select name="perm" id="perm">
        <option value="admin"<?= ($config['min_perm'] == 'admin') ? ' selected="selected"' : '' ?>><?= dgettext('garudaplugin', 'admin') ?></option>
        <option value="dozent"<?= ($config['min_perm'] == 'dozent') ? ' selected="selected"' : '' ?>><?= dgettext('garudaplugin', 'dozent') ?></option>
    </select>
    <br/><br/>
    <?= $this->render_partial('configuration/_studycourses') ?>
    <br/><br/>
    <?= $this->render_partial('configuration/_institutes') ?>
    <br/><br/>
    <div class="submit_wrapper">
        <?= Button::createAccept(dgettext('garudaplugin', 'Einstellungen f�r die aktuell gew�hlte Einrichtung speichern'), 'submit') ?>
    </div>
    <script type="text/javascript">
    //<!--
        STUDIP.Garuda.configOpenSelected();
    //-->
    </script>