<?php if ($i_am_root) { ?>
    <?php if ($one) { ?>
<?= _('Diese Nachricht wird an Personen gesendet, die die folgende Bedingung erfüllen:') ?>
    <?php } else { ?>
<?= _('Diese Nachricht wird an Personen gesendet, die einer der folgenden Bedingungen erfüllen:') ?>
    <?php } ?>
<?php } else { ?>
    <?php if ($one) { ?>
<?= _('Diese Nachricht wird an Personen innerhalb Ihrer Einrichtungen und Studiengänge gesendet, die die folgende Bedingung erfüllen:') ?>
    <?php } else { ?>
<?= _('Diese Nachricht wird an Personen innerhalb Ihrer Einrichtungen und Studiengänge gesendet, die einer der folgenden Bedingungen erfüllen:') ?>
    <?php } ?>
<?php } ?>
