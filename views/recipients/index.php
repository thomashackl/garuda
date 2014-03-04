<h1><?= _('An welche Empf�ngerkreise darf ich schreiben?') ?></h1>
<?php if ($i_am_root) { ?>
<?= formatReady(_('Mit Ihren Root-Rechten d�rfen Sie an alle schreiben!').' :thumb:') ?>
<?php } else { ?>
<h2><?= _('Studieng�nge') ?></h2>
    <?php if ($studycourses) { ?>
<?= $this->render_partial('recipients/_studycourses') ?>
    <?php } else { ?>
    <?= _('Leider wurden keine Studieng�nge als Empf�ngerkreise f�r Sie freigegeben.') ?>
    <?php } ?>
<h2><?= _('Einrichtungen') ?></h2>
    <?php if ($institutes) { ?>
<?= $this->render_partial('recipients/_institutes') ?>
    <?php } else { ?>
    <?= _('Leider wurden keine Einrichtungen als Empf�ngerkreise f�r Sie freigegeben.') ?>
    <?php } ?>
<?php } ?>
<script type="text/javascript">
    //<!--
    STUDIP.Garuda.initRecipientView();
    //-->
</script>
