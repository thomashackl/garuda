<?php if ($i_am_root) { ?>
<?= formatReady(dgettext('garuda', 'Mit Ihren Rechten dürfen Sie an alle schreiben!').' :thumb:') ?>
<?php } else { ?>
<h2><?= dgettext('garuda', 'Studiengänge') ?></h2>
    <?php if ($studycourses) { ?>
<?= $this->render_partial('recipients/_studycourses') ?>
    <?php } else { ?>
    <?= dgettext('garuda', 'Leider wurden keine Studiengänge als Empfängerkreise für Sie freigegeben.') ?>
    <?php } ?>
<h2><?= dgettext('garuda', 'Einrichtungen') ?></h2>
    <?php if ($institutes) { ?>
<?= $this->render_partial('recipients/_institutes') ?>
    <?php } else { ?>
    <?= dgettext('garuda', 'Leider wurden keine Einrichtungen als Empfängerkreise für Sie freigegeben.') ?>
    <?php } ?>
<?php } ?>
<script type="text/javascript">
    //<!--
    STUDIP.Garuda.initRecipientView();
    //-->
</script>
