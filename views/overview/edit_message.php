<form class="default" action="<?= $controller->url_for('overview/save_message') ?>" method="post">
    <?php if ($type == 'template') : ?>
        <section>
            <label>
                <span class="required">
                    <?= dgettext('garudaplugin', 'Name der Vorlage') ?>
                </span>
                <input type="text" name="name" maxlength="255" value="<?= $message->name ?>" required>
            </label>
        </section>
    <?php endif ?>
    <?php if ($message->isNew()) : ?>
        <input type="hidden" name="sendto" value="<?= $flash['sendto'] ?>">
        <?php if ($flash['sendto'] == 'list') : ?>
            <input type="hidden" name="list" value="<?= $flash['list'] ?>">
        <?php endif ?>
        <?php if ($flash['excludelist']) : ?>
            <input type="hidden" name="excludelist" value="<?= $flash['excludelist'] ?>">
        <?php endif ?>
        <?php if ($flash['filters']) : ?>
            <?php foreach ($flash['filters'] as $filter) : ?>
                <input type="hidden" name="filters[]" value="<?= htmlReady($filter) ?>">
            <?php endforeach ?>
        <?php endif ?>
        <?php if ($flash['sendto'] == 'courses' && $flash['courses']) : ?>
            <?php foreach ($flash['courses'] as $course) : ?>
                <input type="hidden" name="courses[]" value="<?= $course ?>">
            <?php endforeach ?>
        <?php endif ?>
        <?php if ($flash['sender']) : ?>
            <input type="hidden" name="sender" value="<?= $flash['sender'] ?>">
            <?php if ($flash['senderid']) : ?>
                <input type="hidden" name="senderid" value="<?= $flash['senderid'] ?>">
            <?php endif ?>
        <?php endif ?>
        <input type="hidden" name="subject" value="<?= $flash['subject'] ?>">
        <input type="hidden" name="message" value="<?= $flash['message'] ?>">
    <?php else : ?>
        <section id="message">
            <label>
                <span class="required">
                    <?= dgettext('garudaplugin', 'Betreff') ?>
                </span>
                <input type="text" name="subject" value="<?= htmlReady($message->subject) ?>"
                       placeholder="<?= dgettext('garudaplugin', 'Geben Sie hier den Betreff Ihrer Nachricht ein.') ?>"
                       size="75" maxlength="255"/>
            </label>
            <label>
                <span class="required">
                    <?= dgettext('garudaplugin', 'Nachrichtentext') ?>
                </span>
                <textarea name="message" placeholder="<?=
                    dgettext('garudaplugin', 'Geben Sie hier den Inhalt Ihrer Nachricht ein.') ?>"
                          data-preview-url="<?= $controller->url_for('message/preview') ?>"
                          cols="75" rows="20"><?= htmlReady($message->message) ?></textarea>
            </label>
        </section>
    <?php endif ?>
    <input type="hidden" name="type" value="<?= $type ?>">
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('garudaplugin', 'Speichern'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(dgettext('garudaplugin', 'Abbrechen'),
            $controller->url_for($type == 'template' ? 'message' : 'overview')) ?>
    </footer>
</form>
