<form class="default garuda-js-init" enctype="multipart/form-data" action="<?= $controller->url_for('message/write') ?>" method="post">
    <header>
        <h1><?= dgettext('garudaplugin', 'Nachricht schreiben') ?></h1>
    </header>
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= dgettext('garudaplugin', 'Empf�ngerkreis') ?></legend>
        <label for="sendto"></label>
        <section>
            <header>
                <?= dgettext('garudaplugin', 'An wen soll die Nachricht gesendet werden?') ?>
            </header>
            <label>
                <input type="radio" name="sendto" value="all" <?= ((!$flash['sendto'] || $flash['sendto'] == 'all') ? ' checked' : '') ?>/>
                <?= dgettext('garudaplugin', 'alle') ?>
            </label>
            <label>
                <input type="radio" name="sendto" value="students" <?= ($flash['sendto'] == 'students' ? ' checked' : '') ?>/>
                <?= dgettext('garudaplugin', 'Studierende') ?>
                <?= tooltipIcon(dgettext('garudaplugin',
                        'Hier�ber werden alle Personen gefunden, die einem '.
                        'oder mehreren Studieng�ngen zugeordnet sind.')) ?>
            </label>
            <label>
                <input type="radio" name="sendto" value="employees" <?= ($flash['sendto'] == 'employees' ? ' checked' : '') ?>/>
                <?= dgettext('garudaplugin', 'Besch�ftigte') ?>
                <?= tooltipIcon(dgettext('garudaplugin',
                    'Hier�ber werden alle Personen gefunden, die mindestens '.
                    'einer Einrichtung zugeordnet sind.')) ?>
            </label>
            <label>
                <input type="radio" name="sendto" value="courses" <?= ($flash['sendto'] == 'courses' ? ' checked' : '') ?>/>
                <?= dgettext('garudaplugin', 'Veranstaltungsteilnehmende') ?>
                <?= tooltipIcon(dgettext('garudaplugin',
                    'Hier�ber werden alle Personen gefunden, die als '.
                    'Teilnehmende in eine der gew�hlten Veranstaltungen '.
                    'eingetragen sind.')) ?>
            </label>
            <div<?= $flash['sendto'] == 'courses' ? '' : ' class="hidden-js"' ?> id="garuda-coursesearch">
                <label>
                    <?= dgettext('garudaplugin', 'Suchen und Hinzuf�gen der gew�nschten Veranstaltungen') ?>
                    <?= $coursesearch ?>
                </label>
                <ul id="garuda-courses">
                    <?php foreach ($courses as $course) : ?>
                        <li>
                            <?= htmlReady(trim($course->veranstaltungsnummer .
                                ' ' . $course->name) . ' (' .
                                $course->start_semester->name . ')') ?>
                            <input type="hidden" name="courses[]" value="<?= $course->id ?>">
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
            <label>
                <?php if ($i_am_root) { ?>
                <input type="radio" name="sendto" value="list" <?= ($flash['sendto'] == 'list' ? ' checked' : '') ?>/>
                <?= dgettext('garudaplugin', 'Manuelle Liste von Nutzernamen') ?>
            </label>
            <label id="reclist">
                <textarea name="list" placeholder="<?= dgettext('garudaplugin',
                    'Tragen Sie hier die Nutzernamen ein, die Ihre Nachricht '.
                    'empfangen sollen (getrennt durch Zeilenumbruch oder Komma)') ?>"
                          cols="80" rows="7"><?= htmlReady($flash['list']) ?></textarea>

            </label>
            <?php } ?>
        </section>
        <section id="filters">
            <span class="filtertext" data-text-src="<?= $controller->url_for('message') ?>">
            <?php if ($flash['sendto'] == 'courses') : ?>
                <?= $this->render_partial('message/sendto_courses') ?>
            <?php elseif (!$filters) : ?>
                <?= $this->render_partial('message/sendto_all') ?>
            <?php else : ?>
                <?php if (sizeof($filters) == 1) : ?>
                    <?= $this->render_partial('message/sendto_filtered', array('one' => true)) ?>
                <?php else : ?>
                    <?= $this->render_partial('message/sendto_filtered') ?>
                <?php endif ?>
            <?php endif ?>
            </span>
            <?php foreach ($filters as $filter) : ?>
                <?= $this->render_partial('userfilter/_show', array('filter' => $filter)) ?>
            <?php endforeach ?>
            <br>
            <?= Studip\Button::create(dgettext('garudaplugin', 'Filter hinzuf�gen'), 'add_filter', array('data-dialog' => '')); ?>
        </section>
    </fieldset>
    <?php if ($i_am_root && !$message) { ?>
    <fieldset>
        <legend><?= dgettext('garudaplugin', 'Personalisierte Teilnahmecodes') ?></legend>
        <section>
            <label>
                <input type="checkbox" name="use_tokens">
                <?= dgettext('garudaplugin', 'Personalisierte Teilnahmecodes o.�. verwenden') ?>
            </label>
        </section>
        <section class="use_tokens hidden-js">
            <label for="tokens"><?= dgettext('garudaplugin',
                'Laden Sie hier eine Textdatei hoch, die die Texte enth�lt, die in '.
                'der Nachricht an jeden einzelnen Empf�nger personalisiert '.
                'verschickt werden sollen (Teilnahmecodes/Links etc.)') ?></label>
            <input name="tokens" type="file" size="40">
        </section>
        <?php if ($messages) { ?>
            <section class="use_tokens hidden-js">
                <label for="message_tokens">
                    <?= dgettext('garudaplugin', 'oder verwenden Sie Tokens aus einer bereits verschickten Nachricht:') ?>
                </label>
                <select name="message_tokens">
                    <option value="">
                        -- <?= dgettext('garudaplugin', 'bitte ausw�hlen') ?> --
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
        <?php // message_id wird zum Upload ben�tigt damit die Dateien eine Zuordnung haben (siehe die upload klasse!) ?>
        <?php $attachment_token = md5(uniqid("neWAtTaChMeNt")) ?>
    <fieldset id="attachments">
        <legend><?= _('Anh�nge') ?></legend>
        <section>
            <input type="hidden" name="message_id" id="message_id" value="<?= htmlReady($attachment_token) ?>">
            <label for="attachments"><?= _('Laden Sie hier Dateianh�nge hoch.') ?></label>
            <div id="attachments">
                <h4><?= _('Anh�nge') ?></h4>
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
                <label>
                    <input type="radio" name="sender" class="garuda-sender-config" value="me"<?=
                        (!$sender || $sender == 'me') ? 'checked' : '' ?>>
                    <?= dgettext('garudaplugin', 'Die Nachricht von meiner Kennung verschicken') ?>
                </label>
            </section>
            <section>
                <label>
                    <input type="radio" name="sender" class="garuda-sender-config" value="person"<?=
                        $sender == 'person' ? 'checked' : '' ?>>
                    <?= dgettext('garudaplugin', 'Eine andere Person als Absender eintragen') ?>
                    <span id="garuda-sendername"<?= ($sender == 'person' && $senderid) ? '' : ' class="hidden-js"' ?>>
                        (<?= $user ? htmlReady($user->getFullname()) . '(' .
                            htmlReady($user->username) . ')' : dgettext('garudaplugin', 'niemand') ?>)
                    </span>
                </label>
                <div id="garuda-sender-choose-person"<?= $sender == 'person' ? '' : ' class="hidden-js"' ?>>
                    <label for="fromsearch_1">
                        <?= dgettext('garudaplugin', 'Alternativen Absender suchen') ?>
                        <?= $fromsearch ?>
                    </label>
                    <input type="hidden" name="senderid" id="garuda-senderid" value="<?= $senderid ?>">
                </div>
            </section>
            <section>
                <label>
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
            <label for="subject"><?= dgettext('garudaplugin', 'Betreff') ?></label>
            <input type="text" name="subject" value="<?= htmlReady($flash['subject']) ?>" placeholder="<?= dgettext('garudaplugin', 'Geben Sie hier den Betreff Ihrer Nachricht ein.') ?>" size="75" maxlength="255"/>
            <label for="message"><?= dgettext('garudaplugin', 'Nachrichtentext') ?></label>
            <textarea name="message" placeholder="<?= dgettext('garudaplugin', 'Geben Sie hier den Inhalt Ihrer Nachricht ein.') ?>" data-preview-url="<?= $controller->url_for('message/preview') ?>" cols="75" rows="20"><?= htmlReady($flash['message']) ?></textarea>
        </section>
        <section id="preview">
            <label>
                <?= dgettext('garudaplugin', 'Vorschau der Nachricht') ?>
                <div id="message_preview_text"></div>
            </label>
        </section>
        <?php if ($i_am_root) { ?>
            <section>
                <label style="clear:both">
                    <input type="checkbox" name="protected"<?= $flash['protected'] ? ' checked' : '' ?>/>
                    <?= dgettext('garudaplugin', 'Beim automatischen Bereinigen soll diese Nachricht nicht entfernt werden') ?>
                </label>
            </section>
        <?php } ?>
    </fieldset>
    <fieldset>
        <legend>
            <?= dgettext('garudaplugin', 'Versandzeitpunkt') ?>
        </legend>
        <section>
            <label>
                <input type="checkbox" name="send_at_date">
                <?= dgettext('garudaplugin', 'Nachricht erst zu einem sp�teren Zeitpunkt verschicken') ?>
            </label>
        </section>
        <section class="send_date hidden-js">
            <label>
                <?= dgettext('garudaplugin', 'Wann soll die Nachricht verschickt werden?') ?>
                <input type="text" name="send_date" size="25" value="<?= $flash['send_date'] ?
                    date('d.m.Y H:i', $flash['send_date']) : date('d.m.Y H:i') ?>">
            </label>
        </section>
    </fieldset>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?php if ($message && Request::isXhr()) : ?>
            <input type="hidden" name="id" value="<?= $message->id ?>">
            <input type="hidden" name="type" value="<?= $message instanceof GarudaTemplate ? 'template' : 'message' ?>">
            <input type="hidden" name="landingpoint" value="<?= $controller->url_for($message instanceof GarudaTemplate ? 'overview/templates' : 'overview/to_send') ?>">
            <?= Studip\Button::createAccept(dgettext('garudaplugin', '�nderungen speichern'), 'store') ?>
            <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('message/write')) ?>
        <?php else : ?>
            <?= Studip\Button::createAccept(dgettext('garudaplugin', 'Nachricht verschicken'), 'submit') ?>
            <?= Studip\Button::create(dgettext('garudaplugin', 'Als Vorlage speichern'),
                'save_template', array('data-dialog' => 'size=auto')) ?>
            <?php if (Config::get()->GARUDA_ENABLE_EXPORT) : ?>
                <?= Studip\Button::create(dgettext('garudaplugin', 'Empf�ngerliste exportieren'), 'export') ?>
            <?php endif ?>
        <?php endif ?>
    </footer>
</form>