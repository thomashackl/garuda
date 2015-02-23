<ul class="collapsable css-tree">
    <?php foreach ($institutes as $i) { ?>
    <li>
        <input type="checkbox" class="tree" id="<?= $i['id'] ?>"/>
        <?php if ($i['is_fak'] && $i['sub_institutes']) { ?>
        <label for="<?= $i['id'] ?>">
        <?php } ?>
            <?= htmlReady($i['name']) ?>
        <?php if ($i['is_fak'] && $i['sub_institutes']) { ?>
        </label>
        <?php } ?>
        <?php if (is_array($i['sub_institutes'])) ?>
        <ul>
            <?php foreach ($i['sub_institutes'] as $s) { ?>
            <li id="inst_<?= $s['id'] ?>">
                <?= htmlReady($s['name']) ?>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </li>
</ul>
