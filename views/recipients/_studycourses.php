<ul id="degrees">
    <?php foreach ($studycourses as $degree => $data) { ?>
        <?php if ($data['name']) { ?>
    <li class="degree">
        <label for="<?= $degree ?>">
            <?= Assets::img('icons/16/blue/arr_1right.png', array('data-toggle-icon' => Assets::image_path('icons/16/blue/arr_1down.png'))) ?>
            <?= htmlReady($data['name']) ?>
        </label>
        <input type="checkbox" class="tree" id="<?= $degree ?>"/>
            <?php if ($data['subjects']) { ?>
        <ul id="subjects_<?= $degree ?>">
                <?php foreach ($data['subjects'] as $subject => $pname) { ?>
            <li class="subject">
                <?= Assets::img('icons/16/black/doctoral_cap.png') ?>
                <?= htmlReady($pname) ?>
            </li>
                <?php } ?>
        </ul>
            <?php } ?>
        <?php } ?>
    </li>
    <?php } ?>
</ul>
