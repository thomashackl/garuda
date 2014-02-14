<?php use Studip\Button, Studip\LinkButton; ?>
<form id="filterform" action="<?= $controller->url_for('message/save_filter') ?>" method="post">
    <h2><?= _('Welche Personen sollen erfasst werden?') ?></h2>
    <div id="filterfields">
        <div class="filterfield">
            <select name="field[]" data-config-url="<?= $controller->url_for('message/filter_config') ?>" onchange="STUDIP.Garuda.getFilterConfig(this)">
                <option value="">-- <?= _('bitte ausw�hlen') ?> --</option>
        <?php foreach ($filterfields as $className => $displayName) { ?>
                <option value="<?= $className ?>"><?= htmlReady($displayName) ?></option>
        <?php } ?>
            </select>
            <span class="fieldconfig"></span>
        </div>
    </div>
    <br/>
    <div class="filter_action">
        <?= Button::create(_('Bedingung hinzuf�gen'), array('id' => 'add_field')) ?>
    </div>
    <br/>
    <div class="submit_wrapper">
        <?= CSRFProtection::tokenTag() ?>
        <?= Button::createAccept(_('Filter �bernehmen'), array('name' => 'submit')) ?>
    </div>
</form>
<script type="text/javascript">
//<!--
    STUDIP.Garuda.filterInit();
//-->
</script>
