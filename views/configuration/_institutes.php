    <label class="caption" for="institutes[]">
        <?= dgettext('garudaplugin', 'An welche Einrichtungen (außer der/den eigenen) soll die gewählte Einrichtung Nachrichten schreiben dürfen?') ?>
    </label>
    <ul id="faculties">
        <?php $first = true; ?>
        <?php foreach ($institutes as $i) { ?>
            <?php if ($i['is_fak']) { ?>
                <?php
                    if ($first) {
                        $first = false;
                    } else {
                ?>
            </ul>
        </li>
                <?php } ?>
        <li class="faculty">
            <input type="checkbox" name="institutes[]" class="faculty_select" value="<?= $i['Institut_id'] ?>"<?= ($config['institutes'][$i['Institut_id']]) ? ' checked="checked"' : '' ?>/>
            <label for="<?= $i['Institut_id'] ?>">
                <?= Assets::img('icons/16/blue/institute.png') ?>
                <?= htmlReady($i['Name']) ?>
            </label>
            <span class="actions" id="actions_<?= $i['Institut_id'] ?>" style="display: none">
                (
                <?= dgettext('garudaplugin', 'markieren:') ?>
                <a class="all"><?= dgettext('garudaplugin', 'alle') ?></a>
                |
                <a class="none"><?= dgettext('garudaplugin', 'keine') ?></a>
                )
            </span>
            <input type="checkbox" class="tree" id="<?= $i['Institut_id'] ?>"/>
            <ul id="institutes_<?= $i['Institut_id'] ?>">
            <?php } else { ?>
                <li class="institute">
                    <?php $o=new Institute($i['Institut_id']); ?>
                    <input type="checkbox" class="subtree" name="institutes[]" value="<?= $i['Institut_id'] ?>"<?= ($config['institutes'][$o->fakultaets_id] ? ' checked="checked" disabled="disabled"' : ($config['institutes'][$i['Institut_id']] ? ' checked="checked"' : '')) ?> data-faculty-id="<?= $o->fakultaets_id ?>"/>
                    <?= htmlReady($i['Name']) ?>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>