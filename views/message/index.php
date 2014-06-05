<?php
use Studip\Button, Studip\LinkButton;
if ($flash['success']) {
    echo MessageBox::success($flash['success']);
}
if ($flash['error']) {
    echo MessageBox::error($flash['error']);
}
?>
<h1><?= dgettext('garudaplugin', 'Nachricht schreiben') ?></h1>
<form class="studip_form" action="<?= $controller->url_for('message') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= dgettext('garudaplugin', 'Empf�ngerkreis') ?></legend>
        <label class="caption" for="sendto"><?= dgettext('garudaplugin', 'An wen soll die Nachricht gesendet werden?') ?></label>
        <input type="radio" name="sendto" value="all" <?= ((!$flash['sendto'] || $flash['sendto'] == 'all') ? ' checked="checked"' : '') ?>/> <?= dgettext('garudaplugin', 'alle') ?>
        <br/>
        <input type="radio" name="sendto" value="students" <?= ($flash['sendto'] == 'students' ? ' checked="checked"' : '') ?>/> <?= dgettext('garudaplugin', 'Studierende') ?>
        <br/>
        <input type="radio" name="sendto" value="employees" <?= ($flash['sendto'] == 'employees' ? ' checked="checked"' : '') ?>/> <?= dgettext('garudaplugin', 'Besch�ftigte') ?>
        <br/>
        <?php if ($i_am_root) { ?>
        <input type="radio" name="sendto" value="list" <?= ($flash['sendto'] == 'list' ? ' checked="checked"' : '') ?>/> <?= dgettext('garudaplugin', 'Manuelle Liste von Nutzernamen') ?>
        <br/>
        <?php } ?>
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
        <?= Button::create(dgettext('garudaplugin', 'Filter hinzuf�gen'), 'add_filter', array('rel' => 'lightbox')); ?>
    </fieldset>
    <fieldset id="reclist">
        <legend><?= dgettext('garudaplugin', 'Manuell gesetzte Empf�nger') ?></legend>
        <label class="caption" for="list"><?= dgettext('garudaplugin', 'Nutzernamen') ?></label>
        <textarea name="list" cols="50" rows="7" placeholder="<?= dgettext('garudaplugin', 'Tragen Sie hier die Nutzernamen ein, die Ihre Nachricht empfangen sollen (getrennt durch Zeilenumbruch oder Komma)') ?>"><?= htmlReady($flash['list']) ?></textarea>
    </fieldset>
    <fieldset>
        <legend><?= dgettext('garudaplugin', 'Nachrichteninhalt') ?></legend>
        <label class="caption" for="subject"><?= dgettext('garudaplugin', 'Betreff') ?></label>
        <input type="text" name="subject" value="<?= htmlReady($flash['subject']) ?>" placeholder="<?= dgettext('garudaplugin', 'Geben Sie hier den Betreff Ihrer Nachricht ein.') ?>" size="100" maxlength="255"/>
        <label class="caption" for="message"><?= dgettext('garudaplugin', 'Nachrichtentext') ?></label>
        <textarea name="message" placeholder="<?= dgettext('garudaplugin', 'Geben Sie hier den Inhalt Ihrer Nachricht ein.') ?>" cols="100" rows="20"><?= htmlReady($flash['message']) ?></textarea>
    </fieldset>
    <div class="submit_wrapper">
        <?= CSRFProtection::tokenTag() ?>
        <?= Button::createAccept(dgettext('garudaplugin', 'Nachricht verschicken'), 'submit') ?>
    </div>
</form>
<script type="text/javascript">
//<!--
    STUDIP.Garuda.init();
//-->
</script>
