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
<form class="studip_form" action="<?= $controller->url_for('message') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Empfängerkreis') ?></legend>
        <label class="caption" for="sendto"><?= _('An wen soll die Nachricht gesendet werden?') ?></label>
        <input type="radio" name="sendto" value="all" <?= ((!$flash['sendto'] || $flash['sendto'] == 'all') ? ' checked="checked"' : '') ?>/> <?= _('alle') ?>
        <br/>
        <input type="radio" name="sendto" value="students" <?= ($flash['sendto'] == 'students' ? ' checked="checked"' : '') ?>/> <?= _('Studierende') ?>
        <br/>
        <input type="radio" name="sendto" value="employees" <?= ($flash['sendto'] == 'employees' ? ' checked="checked"' : '') ?>/> <?= _('Beschäftigte') ?>
        <br/>
        <div id="filters">
            <span class="filtertext" data-text-src="<?= $controller->url_for('message') ?>">
            <?php if (!$filters) { ?>
                <?= $this->render_partial('message/sendto_all') ?>
            <?php } else { ?>
                <?php if (sizeof($filters) == 1) { ?>
            <?= $this->render_partial('message/sendto_filtered', array('one' => true)) ?>
                <?php } else { ?>
            <?= $this->render_partial('message/sendto_filtered') ?>
                <?php } ?>
            <?php } ?>
            </span>
            <?php foreach ($filters as $filter) { ?>
                <?= $this->render_partial('userfilter/_show', array('filter' => $filter)) ?>
            <?php } ?>
        </div>
        <br/>
        <?= Button::create(_('Filter hinzufügen'), 'add_filter', array('rel' => 'lightbox', 'class' => ((!$flash['sendto'] || $flash['sendto'] == 'all') ? 'hidden-js' : ''))); ?>
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
