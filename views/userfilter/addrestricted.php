<?php use Studip\Button, Studip\LinkButton; ?>
<form class="studip_form" id="filterform" action="<?= $controller->url_for('userfilter/save') ?>" method="post">
    <h2><?= _('Welche Personen sollen erfasst werden?') ?></h2>
    <div id="filterfields">
        <?php foreach ($filterfields as $className => $data) { ?>
        <div class="filterfield">
            <input type="hidden" name="field[]" value="<?= htmlReady($className) ?>"/>
            <label for="value[]" class="caption">
                <?= htmlReady($data['instance']->getName()) ?>
            </label>
            <span class="fieldconfig" id="<?= $className ?>" data-update-url="<?= $controller->url_for('userfilter/restricted_field_config') ?>" data-depends-on="<?= $data['depends_on'] ?>">
                <?= $this->render_partial('userfilter/restricted_field_config', array('field' => $data['instance'])) ?>
            </span>
        </div>
        <?php } ?>
    </div>
    <div class="submit_wrapper">
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
        <?= Button::createAccept(_('Filter übernehmen'), 'submit') ?>
    </div>
</form>
