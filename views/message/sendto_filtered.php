<?php if ($i_am_root) { ?>
    <?php if ($one) { ?>
<?= _('Diese Nachricht wird an Personen gesendet, die die folgende Bedingung erf�llen:') ?>
    <?php } else { ?>
<?= _('Diese Nachricht wird an Personen gesendet, die einer der folgenden Bedingungen erf�llen:') ?>
    <?php } ?>
<?php } else { ?>
    <?php if ($one) { ?>
<?= _('Diese Nachricht wird an Personen innerhalb Ihrer Einrichtungen und Studieng�nge gesendet, die die folgende Bedingung erf�llen:') ?>
    <?php } else { ?>
<?= _('Diese Nachricht wird an Personen innerhalb Ihrer Einrichtungen und Studieng�nge gesendet, die einer der folgenden Bedingungen erf�llen:') ?>
    <?php } ?>
<?php } ?>
