<ul id="institutes">
    <?php foreach ($institutes as $i) { ?>
    <li class="<?= $i['is_fak'] ? 'faculty' : 'institute' ?>" id="inst_<?= $i['id'] ?>">
        <?php if ($i['is_fak'] && $i['sub_institutes']) { ?>
        <label for="<?= $i['id'] ?>">
            <?= Assets::img('icons/16/blue/arr_1right.png', array('data-toggle-icon' => Assets::image_path('icons/16/blue/arr_1down.png'))) ?>
        <?php } else { ?>
            <?= Assets::img('icons/16/black/institute.png') ?>
        <?php } ?>
            <?= htmlReady($i['name']) ?>
        <?php if ($i['is_fak'] && $i['sub_institutes']) { ?>
        </label>
        <input type="checkbox" class="tree" id="<?= $i['id'] ?>"/>
        <?php } ?>
        <?php if ($i['sub_institutes']) ?>
        <ul>
            <?php foreach ($i['sub_institutes'] as $s) { ?>
            <li class="institute" id="inst_<?= $s['id'] ?>">
                <?= Assets::img('icons/16/black/institute.png') ?>
                <?= htmlReady($s['name']) ?>
            </li>
            <?php } ?>
        </ul>
        <?php } ?>
    </li>
</ul>
