<?php use Studip\Button, Studip\LinkButton; ?>
<form class="studip_form" id="filterform" action="<?= $controller->url_for('userfilter/save') ?>" method="post">
    <h2><?= dgettext('garudaplugin', 'Welche Personen sollen erfasst werden?') ?></h2>
    <div id="filterfields">
        <?php foreach ($filterfields as $className => $data) { ?>
        <div class="filterfield" id="<?= $className ?>" data-relation="<?= htmlReady($data['relation']) ?>">
            <label class="caption" for="field[]">
                <?= htmlReady($data['name']) ?>
            </label>
            <input type="hidden" name="field[]" value="<?= $className ?>"/>
            <span class="fieldconfig" data-update-url="<?= $controller->url_for('userfilter/restricted_field_config') ?>">
                <?= $this->render_partial('userfilter/restricted_field_config', array('field' => new $className())) ?>
            </span>
        </div>
        <?php } ?>
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
