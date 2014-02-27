<?php
use Studip\Button;
?>
    <label class="caption" for="perm">
        <?= _('Welche Rechte werden mindestens an dieser Einrichtung ben�tigt, um den Nachrichtenversand nutzen zu d�rfen?') ?>
    </label>
    <select name="perm" id="perm">
        <option value="admin"<?= ($config['min_perm'] == 'admin') ? ' selected="selected"' : '' ?>><?= _('admin') ?></option>
        <option value="dozent"<?= ($config['min_perm'] == 'dozent') ? ' selected="selected"' : '' ?>><?= _('dozent') ?></option>
    </select>
    <br/><br/>
    <?= $this->render_partial('configuration/_studycourses') ?>
    <br/><br/>
    <?= $this->render_partial('configuration/_institutes') ?>
    <br/><br/>
    <div class="submit_wrapper">
        <?= Button::createAccept(_('Einstellungen f�r die aktuell gew�hlte Einrichtung speichern'), 'submit') ?>
    </div>
    <script type="text/javascript">
    //<!--
        STUDIP.Garuda.configOpenSelected();
    //-->
    </script>