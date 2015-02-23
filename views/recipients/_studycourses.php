<ul class="collapsable css-tree">
    <?php foreach ($studycourses as $degree => $data) { ?>
        <?php if ($data['name']) { ?>
    <li>
        <input type="checkbox" class="tree" id="<?= $degree ?>"/>
        <label for="<?= $degree ?>">
            <?= htmlReady($data['name']) ?>
        </label>
            <?php if ($data['subjects']) { ?>
        <ul>
                <?php foreach ($data['subjects'] as $subject => $pname) { ?>
            <li class="subject">
                <?= htmlReady($pname) ?>
            </li>
                <?php } ?>
        </ul>
            <?php } ?>
        <?php } ?>
    </li>
    <?php } ?>
</ul>
