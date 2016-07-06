<form class="default" action="<?= $controller->url_for('message/write/load') ?>" method="post">
    <section>
        <label>
            <?= dgettext('garudaplugin', 'Vorhandene Vorlagen') ?>
            <select name="template">
                <?php foreach ($templates as $t) : ?>
                    <option value="<?= $t->id ?>"><?= htmlReady($t->name) ?></option>
                <?php endforeach ?>
            </select>
        </label>
    </section>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('garudaplugin', 'Laden'), 'load_template') ?>
        <?= Studip\Button::createCancel(_('Abbrechen')) ?>
    </footer>
</form>
