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
<form class="default" enctype="multipart/form-data" action="<?= $controller->url_for('message') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= dgettext('garudaplugin', 'Empfängerkreis') ?></legend>
        <label class="caption" for="sendto"></label>
        <section>
            <header>
                <?= dgettext('garudaplugin', 'An wen soll die Nachricht gesendet werden?') ?>
            </header>
            <label>
                <input type="radio" name="sendto" value="all" <?= ((!$flash['sendto'] || $flash['sendto'] == 'all') ? ' checked="checked"' : '') ?>/>
                <?= dgettext('garudaplugin', 'alle') ?>
            </label>
            <label>
                <input type="radio" name="sendto" value="students" <?= ($flash['sendto'] == 'students' ? ' checked="checked"' : '') ?>/>
                <?= dgettext('garudaplugin', 'Studierende') ?>
            </label>
            <label>
                <input type="radio" name="sendto" value="employees" <?= ($flash['sendto'] == 'employees' ? ' checked="checked"' : '') ?>/>
                <?= dgettext('garudaplugin', 'Beschäftigte') ?>
            </label>
            <label>
                <?php if ($i_am_root) { ?>
                <input type="radio" name="sendto" value="list" <?= ($flash['sendto'] == 'list' ? ' checked="checked"' : '') ?>/>
                <?= dgettext('garudaplugin', 'Manuelle Liste von Nutzernamen') ?>
            </label>
            <?php } ?>
        </section>
        <section id="filters">
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
            <br>
            <?= Button::create(dgettext('garudaplugin', 'Filter hinzufügen'), 'add_filter', array('data-dialog' => '')); ?>
        </section>
    </fieldset>
    <fieldset id="reclist">
        <legend>
            <?= dgettext('garudaplugin', 'Manuell gesetzte Empfänger') ?>
        </legend>
        <label class="caption" for="list"><?= dgettext('garudaplugin', 'Nutzernamen') ?></label>
        <textarea name="list" placeholder="<?= dgettext('garudaplugin', 'Tragen Sie hier die Nutzernamen ein, die Ihre Nachricht empfangen sollen (getrennt durch Zeilenumbruch oder Komma)') ?>" cols="80" rows="7"><?= htmlReady($flash['list']) ?></textarea>
    </fieldset>
    <?php if ($i_am_root) { ?>
    <fieldset>
        <legend><?= dgettext('garudaplugin', 'Liste von Tokens') ?></legend>
        <section>
            <label class="caption" for="tokens"><?= dgettext('garudaplugin',
                'Laden Sie hier eine Textdatei hoch, die die Texte enthält, die in '.
                'der Nachricht an jeden einzelnen Empfänger personalisiert '.
                'verschickt werden sollen (Teilnahmecodes/Links etc.)') ?></label>
            <input name="tokens" type="file" size="40">
        </section>
        <?php if ($messages) { ?>
            <section>
                <label class="caption" for="message_tokens">
                    <?= dgettext('garudaplugin', 'oder verwenden Sie Tokens aus einer bereits verschickten Nachricht:') ?>
                </label>
                <select name="message_tokens">
                    <option value="">
                        -- <?= dgettext('garudaplugin', 'bitte auswählen') ?> --
                    </option>
                    <?php foreach ($messages as $m) { ?>
                    <option value="<?= $m['job_id'] ?>">
                        <?= date('d.m.Y H:i', $m['mkdate']).' '.htmlReady($m['subject']) ?>
                    </option>
                    <?php } ?>
                </select>
            </section>
        <?php } ?>
    </fieldset>
    <?php } ?>
    <?php if ($GLOBALS['ENABLE_EMAIL_ATTACHMENTS']) { ?>
        <?php // message_id wird zum Upload benötigt damit die Dateien eine Zuordnung haben (siehe die upload klasse!) ?>
        <?php $attachment_token = md5(uniqid("neWAtTaChMeNt")) ?>
    <fieldset id="attachments">
        <legend><?= _('Anhänge') ?></legend>
        <section>
            <input type="hidden" name="message_id" id="message_id" value="<?= htmlReady($attachment_token) ?>">
            <label class="caption" for="attachments"><?= _('Laden Sie hier Dateianhänge hoch.') ?></label>
            <div id="attachments">
                <h4><?= _('Anhänge') ?></h4>
                <div>
                    <ul class="files">
                        <li style="display: none;" class="file">
                            <span class="icon"></span>
                            <span class="name"></span>
                            <span class="size"></span>
                            <a class="remove_attachment"><?= Assets::img("icons/16/blue/trash", array('class' => "text-bottom")) ?></a>
                        </li>
                    </ul>
                    <div id="statusbar_container">
                        <div class="statusbar" style="display: none;">
                            <div class="progress"></div>
                            <div class="progresstext">0%</div>
                        </div>
                    </div>
                    <label style="cursor: pointer;">
                        <input type="file" id="fileupload" multiple onChange="STUDIP.Messages.upload_from_input(this);" style="display: none;">
                        <?= Assets::img("icons/20/blue/upload", array('title' => _("Datei hochladen"), 'class' => "text-bottom")) ?>
                        <?= _("Datei hochladen") ?>
                    </label>

                    <div id="upload_finished" style="display: none"><?= _("wird verarbeitet") ?></div>
                    <div id="upload_received_data" style="display: none"><?= _("gespeichert") ?></div>
                </div>
            </div>
        </section>
    </fieldset>
    <?php } ?>
    <?php if ($i_am_root) { ?>
        <fieldset>
            <legend><?= dgettext('garudaplugin', 'Absender') ?></legend>
            <section>
                <label class="caption">
                    <input type="radio" name="sender" class="garuda-sender-config" value="me"<?=
                        (!$sender || $sender == 'me') ? 'checked' : '' ?>>
                    <?= dgettext('garudaplugin', 'Die Nachricht von meiner Kennung verschicken') ?>
                </label>
            </section>
            <section>
                <label class="caption">
                    <input type="radio" name="sender" class="garuda-sender-config" value="person"<?=
                        $sender == 'person' ? 'checked' : '' ?>>
                    <?= dgettext('garudaplugin', 'Eine andere Person als Absender eintragen') ?>
                    <span id="garuda-sendername"<?= ($sender == 'person' && $senderid) ? '' : ' class="hidden-js"' ?>>
                        (<?= $user ? htmlReady($user->getFullname()) . '(' .
                            htmlReady($user->username) . ')' : dgettext('garudaplugin', 'niemand') ?>)
                    </span>
                </label>
                <div id="garuda-sender-choose-person"<?= $sender == 'person' ? '' : ' class="hidden-js"' ?>>
                    <label class="caption" for="fromsearch_1">
                        <?= dgettext('garudaplugin', 'Alternativen Absender suchen') ?>
                        <?= $fromsearch ?>
                    </label>
                    <input type="hidden" name="senderid" id="garuda-senderid" value="<?= $senderid ?>">
                </div>
            </section>
            <section>
                <label class="caption">
                    <input type="radio" name="sender" class="garuda-sender-config" value="system"<?=
                        $sender == 'system' ? 'checked' : ''?>>
                    <?= dgettext('garudaplugin', 'Anonym, mit "Stud.IP" als Absender verschicken') ?>
                </label>
            </section>
        </fieldset>
    <?php } ?>
    <fieldset>
        <legend><?= dgettext('garudaplugin', 'Nachrichteninhalt') ?></legend>
        <section id="message">
            <label class="caption" for="subject"><?= dgettext('garudaplugin', 'Betreff') ?></label>
            <input type="text" name="subject" value="<?= htmlReady($flash['subject']) ?>" placeholder="<?= dgettext('garudaplugin', 'Geben Sie hier den Betreff Ihrer Nachricht ein.') ?>" size="75" maxlength="255"/>
            <label class="caption" for="message"><?= dgettext('garudaplugin', 'Nachrichtentext') ?></label>
            <textarea name="message" placeholder="<?= dgettext('garudaplugin', 'Geben Sie hier den Inhalt Ihrer Nachricht ein.') ?>" data-preview-url="<?= $controller->url_for('message/preview') ?>" cols="75" rows="20"><?= htmlReady($flash['message']) ?></textarea>
        </section>
        <section id="preview">
            <label class="caption" for="message_preview_text">
                <?= dgettext('garudaplugin', 'Vorschau der Nachricht') ?>
            </label>
            <div id="message_preview_text"></div>
        </section>
        <?php if ($i_am_root) { ?>
            <section>
                <label class="caption" style="clear:both">
                    <input type="checkbox" name="protected"<?= $flash['protected'] ? ' checked="checked"' : '' ?>/>
                    <?= dgettext('garudaplugin', 'Beim automatischen Bereinigen soll diese Nachricht nicht entfernt werden') ?>
                </label>
            </section>
        <?php } ?>
    </fieldset>
    <?= CSRFProtection::tokenTag() ?>
    <section data-dialog-button>
        <?= Button::createAccept(dgettext('garudaplugin', 'Nachricht verschicken'), 'submit') ?>
    </section>
</form>
<script type="text/javascript">
//<!--
    STUDIP.Garuda.init();
//-->
</script>
