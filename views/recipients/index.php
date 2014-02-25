<h1><?= _('An welche Empf�ngerkreise darf ich schreiben?') ?></h1>
<?php if ($i_am_root) { ?>
<?= formatReady(_('Mit Ihren Root-Rechten d�rfen Sie an alle schreiben!').' :thumb:') ?>
<?php } else { ?>
<h2><?= _('Studieng�nge') ?></h2>
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
            <?php if ($data['professions']) { ?>
        <ul id="professions_<?= $degree ?>">
                <?php foreach ($data['professions'] as $profession => $pname) { ?>
            <li class="profession">
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
    <?= _('Leider wurden keine Studieng�nge als Empf�ngerkreise f�r Sie freigegeben.') ?>
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
            <?php if ($data['professions']) { ?>
        <ul id="professions_<?= $degree ?>">
                <?php foreach ($data['professions'] as $profession => $pname) { ?>
            <li class="profession">
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
    <?= _('Leider wurden keine Einrichtungen als Empf�ngerkreise f�r Sie freigegeben.') ?>
    <?php } ?>
<?php } ?>
