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
    <?php } else { ?>
    <?= _('Leider wurden keine Einrichtungen als Empfängerkreise für Sie freigegeben.') ?>
    <?php } ?>
<?php } ?>
<script type="text/javascript">
    //<!--
    STUDIP.Garuda.initRecipientView();
    //-->
</script>
