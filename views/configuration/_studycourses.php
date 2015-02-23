    <label class="caption" for="studycourse[]">
        <?= dgettext('garudaplugin', 'An welche Studiengänge soll die gewählte Einrichtung Nachrichten schreiben dürfen?') ?>
    </label>
    <ul class="collapsable css-tree">
        <li>
            <input type="checkbox" class="tree" id="root_degree"/>
            <label for="root_degree">
                <?= htmlReady($GLOBALS['UNI_NAME']) ?>
            </label>
            <ul>
            <?php foreach ($degrees as $degree) { ?>
                <?php if ($degree['name'] && $degree['profession']) { ?>
                <li>
                    <input type="checkbox" class="tree" id="<?= $degree['abschluss_id'] ?>"/>
                    <label for="<?= $degree['abschluss_id'] ?>">
                        <?= htmlReady($degree['name']) ?>
                    </label>
                    <span class="actions" id="actions_<?= $degree['abschluss_id']?>">
                        (
                        <?= dgettext('garudaplugin', 'markieren:') ?>
                        <a class="all"><?= dgettext('garudaplugin', 'alle') ?></a>
                        |
                        <a class="none"><?= dgettext('garudaplugin', 'keine') ?></a>
                        )
                    </span>
                    <?php if ($degree['profession']) { ?>
                    <ul>
                        <?php foreach ($degree['profession'] as $subject) { ?>
                        <li>
                            <input type="checkbox" class="selector" name="studycourses[]" value="<?= $degree['abschluss_id'].'|'.$subject['studiengang_id'] ?>"<?= ($config['studycourses'][$degree['abschluss_id']][$subject['studiengang_id']]) ? ' checked="checked"' : '' ?> data-degree-id="<?= $degree['abschluss_id'] ?>"/>
                            <?= htmlReady($subject['name']) ?>
                        </li>
                        <?php } ?>
                    </ul>
                    <?php } ?>
                </li>
                <?php } ?>
            <?php } ?>
            </ul>
        </li>
    </ul>
