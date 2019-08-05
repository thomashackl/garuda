<form id="filterform" action="<?= $controller->url_for('userfilter/save') ?>" method="post"<?= $xhr ? ' data-dialog="size=auto"' : '' ?>>
    <h2><?= dgettext('garuda', 'Welche Personen sollen erfasst werden?') ?></h2>
    <section id="filterfields">
        <div class="filterfield">
            <select name="field[]" data-config-url="<?= $controller->url_for('userfilter/field_config') ?>" onchange="STUDIP.Garuda.getFilterConfig(this)">
                <option value="">-- <?= dgettext('garuda', 'bitte auswählen') ?> --</option>
        <?php foreach ($filterfields as $className => $displayName) : ?>
                <option value="<?= $className ?>"><?= htmlReady($displayName) ?></option>
        <?php endforeach ?>
            </select>
            <span class="fieldconfig"></span>
        </div>
    </section>
    <section class="filter_action">
        <?= Studip\Button::create(dgettext('garuda', 'Bedingung hinzufügen'), array('id' => 'add_field')) ?>
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
        <?= Studip\Button::createAccept(dgettext('garuda', 'Filter übernehmen'),
            'submit') ?>
        <?= Studip\LinkButton::createCancel(dgettext('garuda', 'Abbrechen'),
            $controller->url_for('message/write')) ?>
    </footer>
</form>
<script type="text/javascript">
//<!--
    STUDIP.Garuda.initFilter();
//-->
</script>
