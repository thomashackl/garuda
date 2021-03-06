<form class="default garuda-js-init" enctype="multipart/form-data" action="<?= $controller->url_for('message/write') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= dgettext('garuda', 'Empfängerkreis') ?></legend>
        <label for="sendto"></label>
        <section>
            <header>
                <?= dgettext('garuda', 'An wen soll die Nachricht gesendet werden?') ?>
            </header>
            <?php if ($allowStudycourses || count($institutes) > 0) : ?>
                <label>
                    <input type="radio" name="sendto" value="all" <?= ((!$flash['sendto'] || $flash['sendto'] == 'all') ? ' checked' : '') ?>/>
                    <?= dgettext('garuda', 'alle') ?>
                </label>
            <?php endif ?>
            <?php if ($allowStudycourses) : ?>
                <label>
                    <input type="radio" name="sendto" value="students" <?= ($flash['sendto'] == 'students' ? ' checked' : '') ?>/>
                    <?= dgettext('garuda', 'Studierende') ?>
                    <?= tooltipIcon(dgettext('garuda',
                            'Hierüber werden alle Personen gefunden, die einem '.
                            'oder mehreren Studiengängen zugeordnet sind.')) ?>
                </label>
            <?php endif ?>
            <?php if (count($institutes) > 0) : ?>
                <label>
                    <input type="radio" name="sendto" value="employees" <?= ($flash['sendto'] == 'employees' ? ' checked' : '') ?>/>
                    <?= dgettext('garuda', 'Beschäftigte') ?>
                    <?= tooltipIcon(dgettext('garuda',
                        'Hierüber werden alle Personen gefunden, die mindestens '.
                        'einer Einrichtung zugeordnet sind.')) ?>
                </label>
            <?php endif ?>
            <label>
                <input type="radio" name="sendto" value="courses" <?= ($flash['sendto'] == 'courses' ? ' checked' : '') ?>/>
                <?= dgettext('garuda', 'Veranstaltungsteilnehmende') ?>
                <?= tooltipIcon(dgettext('garuda',
                    'Hierüber werden alle Personen gefunden, die als '.
                    'Teilnehmende in eine der gewählten Veranstaltungen '.
                    'eingetragen sind.')) ?>
            </label>
            <div<?= $flash['sendto'] == 'courses' ? '' : ' class="hidden-js"' ?> id="garuda-coursesearch">
                <label>
                    <?= dgettext('garuda', 'Suchen und Hinzufügen der gewünschten Veranstaltungen') ?>
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
                <?= dgettext('garuda', 'Manuelle Liste von Nutzernamen') ?>
            </label>
            <label id="reclist">
                <textarea name="list" placeholder="<?= dgettext('garuda',
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
            <?= Studip\Button::create(dgettext('garuda', 'Filter hinzufügen'), 'add_filter', array('data-dialog' => '')); ?>
        </section>
        <section id="exclude">
            <label>
                <input type="checkbox" name="exclude"<?= $flash['excludelist'] ? ' checked' : ''?>>
                <?= dgettext('garuda', 'Personen ausschließen') ?>
            </label>
            <label id="excludelist">
                <textarea name="excludelist" placeholder="<?= dgettext('garuda',
                    'Tragen Sie hier die Nutzernamen ein, die Ihre Nachricht NICHT '.
                    'empfangen sollen (getrennt durch Zeilenumbruch oder Komma)') ?>"
                          cols="80" rows="7"><?= htmlReady($flash['excludelist']) ?></textarea>

            </label>
        </section>
    </fieldset>
    <fieldset>
        <legend>
            <?= dgettext('garuda', 'In Kopie an') ?>
        </legend>
        <section id="cc">
            <?= $ccsearch ?>
            <ul id="garuda-cc">
            <?php if (is_array($cc) && count($cc) > 0) : ?>
                <?php foreach ($cc as $one) : ?>
                <li class="garuda-cc-user">
                    <?= $one->getFullname() ?> (<?= $one->username ?>)
                    <input type="hidden" name="cc[]" value="<?= $one->id ?>">
                </li>
                <?php endforeach ?>
            <?php endif ?>
            </ul>
        </section>
    </fieldset>
    <?php if ($i_am_root) { ?>
    <fieldset>
        <legend><?= dgettext('garuda', 'Personalisierte Teilnahmecodes') ?></legend>
        <section>
            <label>
                <input type="checkbox" name="use_tokens" value="1"<?= $flash['use_tokens'] ? ' checked' : '' ?>>
                <?= dgettext('garuda', 'Personalisierte Teilnahmecodes o.ä. verwenden') ?>
            </label>
        </section>
        <section class="use-tokens<?= $flash['use_tokens'] ? '' : ' hidden-js' ?>">
            <label for="token-file"><?= dgettext('garuda',
                'Laden Sie hier eine Textdatei hoch, die die Texte enthält, die in '.
                'der Nachricht an jeden einzelnen Empfänger personalisiert '.
                'verschickt werden sollen (Teilnahmecodes/Links etc.)') ?></label>
            <div id="tokens">
                <div>
                    <ul class="files">
                        <li style="display: none;" class="file">
                            <span class="icon"></span>
                            <span class="name"></span>
                            <span class="size"></span>
                            <a class="remove-file"><?= Icon::create('trash', 'clickable', ['class' => 'text-bottom']) ?></a>
                        </li>
                        <?php if (is_array($tokens)) : ?>
                            <?php foreach ($tokens as $t) : ?>
                                <li class="file" data-document-id="<?= $t['document_id'] ?>">
                                    <span class="icon"><?= $t['icon'] ?></span>
                                    <span class="name"><?= $t['name'] ?></span>
                                    <span class="size"><?= $t['size'] ?></span>
                                    <a class="remove-file"><?= Icon::create('trash', 'clickable', ['class' => 'text-bottom']) ?></a>
                                </li>
                            <?php endforeach ?>
                        <?php endif ?>
                    </ul>
                    <div id="tokens-statusbar-container">
                        <div class="statusbar" style="display: none;">
                            <div class="progress"></div>
                            <div class="progresstext">0%</div>
                        </div>
                    </div>
                    <label style="cursor: pointer;">
                        <input type="file" id="tokens-fileupload" onChange="STUDIP.Garuda.uploadFromInput(this, 'tokens');" style="display: none;">
                        <?= Icon::create('upload', 'clickable', ['title' => dgettext('garuda', 'Datei hochladen'), 'class' => 'text-bottom'])->asImg(20) ?>
                        <?= dgettext('garuda', "Datei hochladen") ?>
                    </label>

                    <div id="tokens-upload-finished" style="display: none"><?= dgettext('garuda', "wird verarbeitet") ?></div>
                    <div id="tokens-upload-received-data" style="display: none"><?= dgettext('garuda', "gespeichert") ?></div>
                </div>
            </div>
        </section>
        <?php if ($messages) { ?>
            <section class="use-tokens hidden-js">
                <label for="message_tokens">
                    <?= dgettext('garuda', 'oder verwenden Sie Tokens aus einer bereits verschickten Nachricht:') ?>
                </label>
                <select name="message_tokens">
                    <option value="">
                        -- <?= dgettext('garuda', 'bitte auswählen') ?> --
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
    <?php if ($GLOBALS['ENABLE_EMAIL_ATTACHMENTS']) : ?>
        <fieldset id="attachments">
            <legend><?= dgettext('garuda', 'Anhänge') ?></legend>
            <section>
                <label for="attachments"><?= dgettext('garuda', 'Laden Sie hier Dateianhänge hoch.') ?></label>
                <div id="attachments">
                    <h4><?= dgettext('garuda', 'Anhänge') ?></h4>
                    <div>
                        <ul class="files">
                            <li style="display: none;" class="file">
                                <span class="icon"></span>
                                <span class="name"></span>
                                <span class="size"></span>
                                <a class="remove-file"><?= Icon::create('trash', 'clickable', ['class' => 'text-bottom']) ?></a>
                            </li>
                            <?php if (is_array($default_attachments)) : ?>
                                <?php foreach ($default_attachments as $a) : ?>
                                    <li class="file" data-document-id="<?=$a['document_id']?>">
                                        <span class="icon"><?=$a['icon']?></span>
                                        <span class="name"><?=$a['name']?></span>
                                        <span class="size"><?=$a['size']?></span>
                                        <a class="remove-file"><?= Icon::create('trash', 'clickable', ['class' => 'text-bottom']) ?></a>
                                    </li>
                                <?php endforeach ?>
                            <?php endif ?>
                        </ul>
                        <div id="attachments-statusbar-container">
                            <div class="statusbar" style="display: none;">
                                <div class="progress"></div>
                                <div class="progresstext">0%</div>
                            </div>
                        </div>
                        <label style="cursor: pointer;">
                            <input type="file" id="attachments-fileupload" multiple onChange="STUDIP.Garuda.uploadFromInput(this, 'attachments');" style="display: none;">
                            <?= Icon::create('upload', 'clickable', ['title' => dgettext('garuda', 'Datei hochladen'), 'class' => 'text-bottom'])->asImg(20) ?>
                            <?= dgettext('garuda', "Datei hochladen") ?>
                        </label>

                        <div id="attachments-upload-finished" style="display: none"><?= dgettext('garuda', "wird verarbeitet") ?></div>
                        <div id="attachments-upload-received-data" style="display: none"><?= dgettext('garuda', "gespeichert") ?></div>
                    </div>
                </div>
            </section>
        </fieldset>
    <?php endif ?>
    <?php if ($i_am_root) { ?>
        <fieldset>
            <legend><?= dgettext('garuda', 'Absender') ?></legend>
            <section>
                <label>
                    <input type="radio" name="sender" class="garuda-sender-config" value="me"<?=
                        (!$sender || $sender == 'me') ? 'checked' : '' ?>>
                    <?= dgettext('garuda', 'Die Nachricht von meiner Kennung verschicken') ?>
                </label>
            </section>
            <section>
                <label>
                    <input type="radio" name="sender" class="garuda-sender-config" value="person"<?=
                        $sender == 'person' ? 'checked' : '' ?>>
                    <?= dgettext('garuda', 'Eine andere Person als Absender eintragen') ?>
                    <span id="garuda-sendername"<?= ($sender == 'person' && $senderid) ? '' : ' class="hidden-js"' ?>>
                        (<?= $user ? htmlReady($user->getFullname()) . '(' .
                            htmlReady($user->username) . ')' : dgettext('garuda', 'niemand') ?>)
                    </span>
                </label>
                <div id="garuda-sender-choose-person"<?= $sender == 'person' ? '' : ' class="hidden-js"' ?>>
                    <label for="fromsearch_1">
                        <?= dgettext('garuda', 'Alternativen Absender suchen') ?>
                        <?= $fromsearch ?>
                    </label>
                    <input type="hidden" name="senderid" id="garuda-senderid" value="<?= $senderid ?>">
                </div>
            </section>
            <section>
                <label>
                    <input type="radio" name="sender" class="garuda-sender-config" value="system"<?=
                        $sender == 'system' ? 'checked' : ''?>>
                    <?= dgettext('garuda', 'Anonym, mit "Stud.IP" als Absender verschicken') ?>
                </label>
            </section>
        </fieldset>
    <?php } ?>
    <fieldset>
        <legend><?= dgettext('garuda', 'Nachrichteninhalt') ?></legend>
        <section id="message">
            <label for="subject">
                <span class="required">
                    <?= dgettext('garuda', 'Betreff') ?>
                </span>
                <input type="text" name="subject" value="<?= htmlReady($flash['subject']) ?>" placeholder="<?= dgettext('garuda', 'Geben Sie hier den Betreff Ihrer Nachricht ein.') ?>" size="75" maxlength="255"/>
            </label>
            <label id="garuda-markers">
                <?= dgettext('garuda', 'Feld für Serienmail einfügen') ?>
                <select name="markers">
                    <option value="" data-description="">-- <?= dgettext('garuda', 'bitte auswählen') ?> --</option>
                    <?php foreach ($markers as $marker) : ?>
                        <?php if ($GLOBALS['perm']->have_perm($marker->permission)) : ?>
                            <option value="###<?= $marker->marker ?>###" data-description="<?= htmlReady(nl2br($marker->description)) ?>"><?= htmlReady($marker->name) ?></option>
                        <?php endif ?>
                    <?php endforeach ?>
                </select>
                <?= Studip\LinkButton::createAccept(dgettext('garuda', 'Einsetzen'), '', array('id' => 'garuda-add-marker', 'class' => 'hidden-js')) ?>
                <div id="garuda-marker-description"></div>
            </label>
            <label for="message">
                <span class="required">
                    <?= dgettext('garuda', 'Nachrichtentext') ?>
                </span>
            </label>
            <textarea name="message" class="add_toolbar wysiwyg size-l" id="message"
                      placeholder="<?= dgettext('garuda', 'Geben Sie hier den Inhalt Ihrer Nachricht ein.') ?>"
                      <?= !$wysiwyg ? 'data-preview-url="' . $controller->url_for('message/preview') . '"' : '' ?>"
                      cols="75" rows="20"><?= htmlReady($flash['message']) ?></textarea>
        </section>
        <?php if (!$wysiwyg) : ?>
            <section id="preview">
                <label>
                    <?= dgettext('garuda', 'Vorschau der Nachricht') ?>
                    <div id="message_preview_text"></div>
                </label>
            </section>
        <?php endif ?>
        <?php if ($i_am_root) { ?>
            <section>
                <label style="clear:both">
                    <input type="checkbox" name="protected"<?= $flash['protected'] ? ' checked' : '' ?>/>
                    <?= dgettext('garuda', 'Beim automatischen Bereinigen soll diese Nachricht nicht entfernt werden') ?>
                </label>
            </section>
        <?php } ?>
    </fieldset>
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= dgettext('garuda', 'Versandzeitpunkt') ?>
        </legend>
        <section>
            <label>
                <input type="checkbox" name="send_at_date"<?= $flash['send_at_date'] ? ' checked' : '' ?>>
                <?= dgettext('garuda', 'Nachricht erst zu einem späteren Zeitpunkt verschicken') ?>
            </label>
        </section>
        <section class="send_date<?= $flash['send_at_date'] ? '' : ' hidden-js' ?>">
            <label>
                <?= dgettext('garuda', 'Wann soll die Nachricht verschickt werden?') ?>
                <input type="text" name="send_date" size="25" value="<?= $flash['send_date'] ?
                    date('d.m.Y H:i', $flash['send_date']) : date('d.m.Y H:i') ?>">
            </label>
        </section>
    </fieldset>
    <footer data-dialog-button>
        <input type="hidden" name="message_id" id="message-id" value="<?= $message ? $message->id : '' ?>">
        <input type="hidden" name="provisional_id" id="provisional-id" value="<?= $provisional_id ?>">
        <input type="hidden" name="type" value="<?= $type ?>">
        <?php if ($message && Request::isXhr()) : ?>
            <input type="hidden" name="landingpoint" value="<?= $controller->url_for($type == 'template' ? 'overview/templates' : 'overview/to_send') ?>">
            <?= Studip\Button::createAccept(dgettext('garuda', 'Änderungen speichern'), 'store') ?>
            <?= Studip\LinkButton::createCancel(dgettext('garuda', 'Abbrechen'), $controller->url_for('message/write')) ?>
        <?php else : ?>
            <?= Studip\Button::createAccept(dgettext('garuda', 'Nachricht verschicken'), 'submit') ?>
            <?= Studip\Button::create(dgettext('garuda', 'Als Vorlage speichern'),
                'save_template', array('data-dialog' => 'size=auto')) ?>
            <?php if (Config::get()->GARUDA_ENABLE_EXPORT) : ?>
                <?= Studip\Button::create(dgettext('garuda', 'Empfängerliste exportieren'), 'export') ?>
            <?php endif ?>
        <?php endif ?>
    </footer>
</form>
