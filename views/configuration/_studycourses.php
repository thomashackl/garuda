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
            <ul id="subjects_<?= $degree['abschluss_id'] ?>">
                    <?php foreach ($degree['profession'] as $subject) { ?>
                <li class="subject">
                    <input type="checkbox" class="subtree" name="studycourses[]" value="<?= $degree['abschluss_id'].'|'.$subject['studiengang_id'] ?>"<?= ($config['studycourses'][$degree['abschluss_id']][$subject['studiengang_id']]) ? ' checked="checked"' : '' ?> data-degree-id="<?= $degree['abschluss_id'] ?>"/>
                    <?= htmlReady($subject['name']) ?>
                </li>
                    <?php } ?>
            </ul>
                <?php } ?>
            <?php } ?>
        </li>
        <?php } ?>
    </ul>