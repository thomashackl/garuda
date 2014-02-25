<?php
if ($flash['success']) {
    echo MessageBox::success($flash['success']);
}
if ($flash['error']) {
    echo MessageBox::error($flash['error']);
}
?>
<h1><?= _('Konfiguration') ?></h1>
<form class="studip_form" action="<?= $controller->url_for('configuration/save') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <label class="caption" for="institute">
        <?= _('Einrichtung wählen, für die Einstellungen gesetzt werden sollen:') ?>
    </label>
    <select name="institute" id="institute" onchange="STUDIP.Garuda.getConfig()" data-update-url="<?= $controller->url_for('configuration/get') ?>">
        <option value="">-- <?= _('bitte auswählen') ?> --</option>
        <?php foreach ($faculties as $faculty) { ?>
        <option value="<?= $faculty->Institut_id ?>"<?= ($flash['institute_id'] == $faculty->Institut_id) ? ' selected="selected"' : '' ?>><?= htmlReady($faculty->name) ?></option>
            <?php foreach ($faculty->sub_institutes as $institute) { ?>
        <option value="<?= $institute->Institut_id ?>"<?= ($flash['institute_id'] == $institute->Institut_id) ? ' selected="selected"' : '' ?>>&nbsp;&nbsp;<?= htmlReady($institute->name) ?></option>
            <?php } ?>
        <?php } ?>
    </select>
    <br/><br/>
    <div id="config"></div>
</form>
<script type="text/javascript">
//<!--
    STUDIP.Garuda.configInit();
//-->
</script>
