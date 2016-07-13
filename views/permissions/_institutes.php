    <label for="institutes[]">
        <?= dgettext('garudaplugin', 'An welche Einrichtungen (außer der/den eigenen) soll die gewählte Einrichtung Nachrichten schreiben dürfen?') ?>
    </label>
    <ul class="collapsable css-tree">
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
        <li>
            <input type="checkbox" name="institutes[]" class="selector" value="<?= $i['Institut_id'] ?>"<?= ($config['institutes'][$i['Institut_id']]) ? ' checked' : '' ?>/>
            <input type="checkbox" class="tree" id="<?= $i['Institut_id'] ?>"/>
            <label for="<?= $i['Institut_id'] ?>" class="undecorated">
                <?= htmlReady($i['Name']) ?>
            </label>
            <span class="actions" id="actions_<?= $i['Institut_id'] ?>">
                (
                <?= dgettext('garudaplugin', 'markieren:') ?>
                <a class="all"><?= dgettext('garudaplugin', 'alle') ?></a>
                |
                <a class="none"><?= dgettext('garudaplugin', 'keine') ?></a>
                )
            </span>
            <ul>
            <?php } else { ?>
                <li>
                    <?php $o=new Institute($i['Institut_id']); ?>
                    <input type="checkbox" class="selector" name="institutes[]" value="<?= $i['Institut_id'] ?>"<?= ($config['institutes'][$o->fakultaets_id] ? ' checked disabled' : ($config['institutes'][$i['Institut_id']] ? ' checked="checked"' : '')) ?> data-faculty-id="<?= $o->fakultaets_id ?>"/>
                    <?= htmlReady($i['Name']) ?>
                </li>
            <?php } ?>
        <?php } ?>
    </ul>
