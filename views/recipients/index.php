<h1><?= _('An welche Empfängerkreise darf ich schreiben?') ?></h1>
<?php if ($i_am_root) { ?>
<?= formatReady(_('Mit Ihren Root-Rechten dürfen Sie an alle schreiben!').' :thumb:') ?>
<?php } else { ?>
<h2><?= _('Studiengänge') ?></h2>
    <?php if ($studycourses) { ?>
<ul id="degrees">
    <?php foreach ($studycourses as $degree => $data) { ?>
        <?php if ($data['name']) { ?>
    <li class="degree">
        <label for="<?= $degree ?>">
            <?= Assets::img('icons/16/blue/doctoral_cap.png') ?>
            <?= htmlReady($data['name']) ?>
        </label>
        <input type="checkbox" class="tree" id="<?= $degree ?>"/>
            <?php if ($data['subjects']) { ?>
        <ul id="subjects_<?= $degree ?>">
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
    <?php } else { ?>
    <?= _('Leider wurden keine Studiengänge als Empfängerkreise für Sie freigegeben.') ?>
    <?php } ?>
<h2><?= _('Einrichtungen') ?></h2>
    <?php if ($institutes) { ?>
<ul id="institutes">
    <?php foreach ($studycourses as $degree => $data) { ?>
        <?php if ($data['name']) { ?>
    <li class="degree">
        <label for="<?= $degree ?>">
            <?= Assets::img('icons/16/blue/doctoral_cap.png') ?>
            <?= htmlReady($data['name']) ?>
        </label>
        <input type="checkbox" class="tree" id="<?= $degree ?>"/>
            <?php if ($data['subjects']) { ?>
        <ul id="subjects_<?= $degree ?>">
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
    <?php } else { ?>
    <?= _('Leider wurden keine Einrichtungen als Empfängerkreise für Sie freigegeben.') ?>
    <?php } ?>
<?php } ?>
