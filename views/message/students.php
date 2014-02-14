<?php use Studip\LinkButton; ?>
    <span class="nofilter">
        <?php if ($i_am_root) { ?>
        <?= _('Diese Nachricht wird an alle Studierenden verschickt.') ?>
        <?php } else { ?>
        <?= _('Diese Nachricht wird an alle Studierenden der für Sie freigegebenen Studiengänge verschickt.') ?>
        <?php } ?>
    </span>
    <br/>
    <?= LinkButton::create(_('Filter hinzufügen'), $controller->url_for('message/add_filter'), array('name' => 'add_filter', 'rel' => 'lightbox')); ?>
