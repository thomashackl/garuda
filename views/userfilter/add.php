<?php use Studip\Button, Studip\LinkButton; ?>
<form class="studip_form" id="filterform" action="<?= $controller->url_for('userfilter/save') ?>" method="post">
    <h2><?= dgettext('garudaplugin', 'Welche Personen sollen erfasst werden?') ?></h2>
    <div id="filterfields">
        <div class="filterfield">
            <select name="field[]" data-config-url="<?= $controller->url_for('userfilter/field_config') ?>" onchange="STUDIP.Garuda.getFilterConfig(this)">
                <option value="">-- <?= dgettext('garudaplugin', 'bitte auswählen') ?> --</option>
        <?php foreach ($filterfields as $className => $displayName) { ?>
                <option value="<?= $className ?>"><?= htmlReady($displayName) ?></option>
        <?php } ?>
            </select>
            <span class="fieldconfig"></span>
        </div>
    </div>
    <br/>
    <div class="filter_action">
        <?= Button::create(dgettext('garudaplugin', 'Bedingung hinzufügen'), array('id' => 'add_field')) ?>
    </div>
    <br/>
    <?php foreach ($flash->flash as $key => $value) { ?>
        <?php if (is_array($value)) { ?>
            <?php foreach ($value as $entry) { ?>
    <input type="hidden" name="<?= htmlReady($key) ?>[]" value="<?= htmlReady($entry) ?>"/>
            <?php } ?>
        <?php } else { ?>
    <input type="hidden" name="<?= htmlReady($key) ?>" value="<?= htmlReady($value) ?>"/>
        <?php } ?>
    <?php } ?>
    <?= CSRFProtection::tokenTag() ?>
    <?= Button::createAccept(dgettext('garudaplugin', 'Filter übernehmen'), 'submit', array('data-dialog-button' => '')) ?>
    <?= LinkButton::createCancel(dgettext('garudaplugin', 'Abbrechen'), $controller->url_for('message'), array('data-dialog-button' => '', 'data-dialog' => 'close')) ?>
</form>
<script type="text/javascript">
//<!--
    STUDIP.Garuda.initFilter();
//-->
</script>
