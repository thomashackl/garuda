<label for="studycourses[]">
    <?= dgettext('garudaplugin', 'An welche Studiengänge soll die gewählte Einrichtung Nachrichten schreiben dürfen?') ?>
</label>
<?php foreach ($studycourses as $degree => $degreedata) : ?>
    <?php
    if ($degreedata['name']) : ?>
        <ul class="collapsable css-tree">
            <li>
                <input type="checkbox" class="tree" id="<?= $degree ?>"/>
                <label for="<?= $degree ?>" class="undecorated">
                    <?= htmlReady($degreedata['name']) ?>
                </label>
                <span class="actions" id="actions_<?= $degree ?>">
                    (
                    <?= dgettext('garudaplugin', 'markieren:') ?>
                    <a class="all"><?= dgettext('garudaplugin', 'alle') ?></a>
                    |
                    <a class="none"><?= dgettext('garudaplugin', 'keine') ?></a>
                    )
                </span>
                <?php if (count($degreedata['subjects']) > 0) : ?>
                <ul>
                    <?php foreach ($degreedata['subjects'] as $subject) : ?>
                    <li>
                        <input type="checkbox" class="selector" name="studycourses[]" value="<?= $degree.'|'.$subject->id ?>"<?= ($config['studycourses'][$degree][$subject->id]) ? ' checked' : '' ?> data-degree-id="<?= $degree ?>"/>
                        <?= htmlReady($subject->name) ?>
                    </li>
                    <?php endforeach ?>
                </ul>
                <?php endif ?>
            </li>
        </ul>
    <?php endif ?>
<?php endforeach ?>
