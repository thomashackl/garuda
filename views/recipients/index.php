<h1><?= _('An welche Empfängerkreise darf ich schreiben?') ?></h1>
<?php if ($i_am_root) { ?>
<?= formatReady(_('Mit Ihren Root-Rechten dürfen Sie an alle schreiben!').' :thumb:') ?>
<?php } else { ?>
<h2><?= _('Studiengänge') ?></h2>
    <?php if ($studycourses) { ?>
<?= $this->render_partial('recipients/_studycourses') ?>
    <?php } else { ?>
    <?= _('Leider wurden keine Studiengänge als Empfängerkreise für Sie freigegeben.') ?>
    <?php } ?>
<h2><?= _('Einrichtungen') ?></h2>
    <?php if ($institutes) { ?>
<?= $this->render_partial('recipients/_institutes') ?>
    <?php } else { ?>
    <?= _('Leider wurden keine Einrichtungen als Empfängerkreise für Sie freigegeben.') ?>
    <?php } ?>
<?php } ?>
<script type="text/javascript">
    //<!--
    STUDIP.Garuda.initRecipientView();
    //-->
</script>
