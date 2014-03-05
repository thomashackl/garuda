<h1><?= dgettext('garudaplugin', 'An welche Empf�ngerkreise darf ich schreiben?') ?></h1>
<?php if ($i_am_root) { ?>
<?= formatReady(dgettext('garudaplugin', 'Mit Ihren Root-Rechten d�rfen Sie an alle schreiben!').' :thumb:') ?>
<?php } else { ?>
<h2><?= dgettext('garudaplugin', 'Studieng�nge') ?></h2>
    <?php if ($studycourses) { ?>
<?= $this->render_partial('recipients/_studycourses') ?>
    <?php } else { ?>
    <?= dgettext('garudaplugin', 'Leider wurden keine Studieng�nge als Empf�ngerkreise f�r Sie freigegeben.') ?>
    <?php } ?>
<h2><?= dgettext('garudaplugin', 'Einrichtungen') ?></h2>
    <?php if ($institutes) { ?>
<?= $this->render_partial('recipients/_institutes') ?>
    <?php } else { ?>
    <?= dgettext('garudaplugin', 'Leider wurden keine Einrichtungen als Empf�ngerkreise f�r Sie freigegeben.') ?>
    <?php } ?>
<?php } ?>
<script type="text/javascript">
    //<!--
    STUDIP.Garuda.initRecipientView();
    //-->
</script>
