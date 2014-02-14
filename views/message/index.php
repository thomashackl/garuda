<?php
use Studip\Button, Studip\LinkButton;
if ($flash['success']) {
    echo MessageBox::success($flash['success']);
}
if ($flash['error']) {
    echo MessageBox::error($flash['error']);
}
?>
<h1><?= _('Nachricht schreiben') ?></h1>
<form class="studip_form" action="<?= $controller->url_for('message/send') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Empfängerkreis') ?></legend>
        <label class="caption" for="sendto"><?= _('An wen soll die Nachricht gesendet werden?') ?></label>
        <input type="radio" name="sendto" value="all" data-update-url="<?= $controller->url_for('message/all') ?>"<?= ((!$flash['sendto'] || $flash['sendto'] == 'all') ? ' checked="checked"' : '') ?>/> <?= _('alle') ?>
        <br/>
        <input type="radio" name="sendto" value="students" data-update-url="<?= $controller->url_for('message/students') ?>"<?= ($flash['sendto'] == 'students' ? ' checked="checked"' : '') ?>/> <?= _('Studierende') ?>
        <br/>
        <input type="radio" name="sendto" value="employees" data-update-url="<?= $controller->url_for('message/employees') ?>"<?= ($flash['sendto'] == 'employees' ? ' checked="checked"' : '') ?>/> <?= _('Beschäftigte') ?>
        <br/>
        <div id="filters">
            <?php if (!$flash['filters']) { ?>
            <span class="nofilter">
                <?php if ($i_am_root) { ?>
                <?= _('Alle Studierenden und Beschäftigten erhalten diese Nachricht.') ?>
                <?php } else { ?>
                <?= _('Alle Studierenden und Beschäftigten innerhalb der für Sie freigegebenen Studiengänge und Einrichtungen erhalten diese Nachricht.') ?>
                <?php } ?>
            </span>
            <?php } else { ?>
                <?php foreach ($flash['filters'] as $filter) { ?>
                <div class="filter">
                    <?= $filter->toString() ?>
                </div>
                <?php } ?>
            <?php } ?>
        </div>
    </fieldset>
    <fieldset>
        <legend><?= _('Nachrichteninhalt') ?></legend>
        <label class="caption" for="subject"><?= _('Betreff') ?></label>
        <input type="text" name="subject" value="<?= htmlReady($flash['subject']) ?>" placeholder="<?= _('Geben Sie hier den Betreff Ihrer Nachricht ein.') ?>" size="100" maxlength="255"/>
        <label class="caption" for="message"><?= _('Nachrichtentext') ?></label>
        <textarea name="message" placeholder="<?= _('Geben Sie hier den Inhalt Ihrer Nachricht ein.') ?>" cols="100" rows="20"><?= htmlReady($flash['message']) ?></textarea>
    </fieldset>
</form>
<div class="submit_wrapper">
    <?= Button::createAccept(_('Nachricht verschicken'), 'submit') ?>
</div>
<script type="text/javascript">
//<!--
    STUDIP.Garuda.init();
//-->
</script>
