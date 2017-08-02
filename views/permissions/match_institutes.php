<form action="<?= $controller->url_for('permissions/assign_institutes') ?>" method="post">
    <?php foreach ($institutes as $i) : ?>
        <div id="<?= htmlReady($i) ?>">
            <label>
                <?= sprintf(dgettext('garudaplugin', '"%s" zuordnen zu:'), $i) ?>
                <select name="institute[<?= $i ?>]" size="1">
                    <?php foreach ($studip_institutes as $s) : ?>
                        <option value="<?= $s['Institut_id'] ?>">
                            <?= htmlReady($s['Name']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </label>
        </div>
    <?php endforeach ?>
</form>
