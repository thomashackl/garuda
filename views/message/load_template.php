<form class="default" action="<?= $controller->url_for('message/write/load') ?>" method="post">
    <section>
        <label>
            <?= dgettext('garuda', 'Vorhandene Vorlagen') ?>
            <select name="template">
                <?php foreach ($templates as $t) : ?>
                    <option value="<?= $t->id ?>"><?= htmlReady($t->name) ?></option>
                <?php endforeach ?>
            </select>
        </label>
    </section>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('garuda', 'Laden'), 'load_template') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('message/write')) ?>
    </footer>
</form>
