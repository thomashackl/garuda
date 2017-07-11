<form class="default" id="filterform" action="<?= $controller->url_for('userfilter/save') ?>" method="post">
    <header>
        <h2>
            <?= dgettext('garudaplugin', 'Welche Personen sollen erfasst werden?') ?>
        </h2>
    </header>
    <section id="filterfields">
        <?php foreach ($filterfields as $className => $data) { ?>
        <div class="filterfield" id="<?= $className ?>" data-relation="<?= htmlReady($data['relation']) ?>">
            <label for="field[]">
                <?= htmlReady($data['name']) ?>
            </label>
            <input type="hidden" name="field[]" value="<?= $className ?>"/>
            <span class="fieldconfig" data-update-url="<?= $controller->url_for('userfilter/restricted_field_config') ?>">
                <?= $this->render_partial('userfilter/restricted_field_config', array('field' => new $className())) ?>
            </span>
        </div>
        <?php } ?>
    </section>
    <section>
        <?php foreach ($flash->flash as $key => $value) : ?>
            <?php if (is_array($value)) : ?>
                <?php foreach ($value as $entry) : ?>
                    <input type="hidden" name="<?= htmlReady($key) ?>[]" value="<?= htmlReady($entry) ?>"/>
                <?php endforeach ?>
            <?php else : ?>
                <input type="hidden" name="<?= htmlReady($key) ?>" value="<?= htmlReady($value) ?>"/>
            <?php endif ?>
        <?php endforeach ?>
    </section>
    <?= CSRFProtection::tokenTag() ?>
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(dgettext('garudaplugin', 'Filter übernehmen'), 'submit') ?>
        <?= Studip\LinkButton::createCancel(dgettext('garudaplugin', 'Abbrechen'), $controller->url_for('message/write'), array('data-dialog-button' => true, 'data-dialog="close"')) ?>
    </footer>
</form>
<script type="text/javascript">
//<!--
    STUDIP.Garuda.initFilter();
//-->
</script>
