<?php
use Studip\Button;
?>
    <label class="caption" for="perm">
        <?= _('Welche Rechte werden mindestens an dieser Einrichtung benötigt, um den Nachrichtenversand nutzen zu dürfen?') ?>
    </label>
    <select name="perm" id="perm">
        <option value="admin"<?= ($config['min_perm'] == 'admin') ? ' selected="selected"' : '' ?>><?= _('admin') ?></option>
        <option value="dozent"<?= ($config['min_perm'] == 'dozent') ? ' selected="selected"' : '' ?>><?= _('dozent') ?></option>
    </select>
    <br/><br/>
    <label class="caption" for="studycourse[]">
        <?= _('An welche Studiengänge soll die gewählte Einrichtung Nachrichten schreiben dürfen?') ?>
    </label>
    <ul id="degrees">
        <?php foreach ($degrees as $degree) { ?>
            <?php if ($degree['name'] && $degree['profession']) { ?>
        <li class="degree">
            <label for="<?= $degree['abschluss_id'] ?>">
                <?= Assets::img('icons/16/blue/doctoral_cap.png') ?>
                <?= htmlReady($degree['name']) ?>
            </label>
            <span class="actions" id="actions_<?= $degree['abschluss_id']?>" style="display: none">
                (
                <?= _('markieren:') ?>
                <a class="all"><?= _('alle') ?></a>
                |
                <a class="none"><?= _('keine') ?></a>
                )
            </span>
            <input type="checkbox" class="tree" id="<?= $degree['abschluss_id'] ?>"/>
                <?php if ($degree['profession']) { ?>
            <ul id="professions_<?= $degree['abschluss_id'] ?>">
                    <?php foreach ($degree['profession'] as $profession) { ?>
                <li class="profession">
                    <input type="checkbox" class="subtree" name="studycourses[]" value="<?= $degree['abschluss_id'].'|'.$profession['studiengang_id'] ?>"<?= ($config['studycourses'][$degree['abschluss_id']][$profession['studiengang_id']]) ? ' checked="checked"' : '' ?> data-degree-id="<?= $degree['abschluss_id'] ?>"/>
                    <?= htmlReady($profession['name']) ?>
                </li>
                    <?php } ?>
            </ul>
                <?php } ?>
            <?php } ?>
        </li>
        <?php } ?>
    </ul>
    <div class="submit_wrapper">
        <?= Button::createAccept(_('Einstellungen für die aktuell gewählte Einrichtung speichern'), 'submit') ?>
    </div>
    <script type="text/javascript">
    //<!--
        STUDIP.Garuda.openSelected();
    //-->
    </script>