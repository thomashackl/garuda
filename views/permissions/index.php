<?php
if ($flash['success']) {
    echo MessageBox::success($flash['success']);
}
if ($flash['error']) {
    echo MessageBox::error($flash['error']);
}
?>
<h1><?= dgettext('garudaplugin', 'Konfiguration') ?></h1>
<form class="default" action="<?= $controller->url_for('permissions/save') ?>" method="post">
    <section>
        <?= CSRFProtection::tokenTag() ?>
        <label>
            <?= dgettext('garudaplugin', 'Einrichtung w�hlen, f�r die Einstellungen gesetzt werden sollen:') ?>
            <select name="institute" id="institute" onchange="STUDIP.Garuda.getConfig()" data-update-url="<?= $controller->url_for('permissions/get') ?>">
                <option value="">
                    -- <?= dgettext('garudaplugin', 'bitte ausw�hlen') ?> --
                </option>
                <?php foreach ($faculties as $faculty) { ?>
                    <option value="<?= $faculty->Institut_id ?>"<?= ($flash['institute_id'] == $faculty->Institut_id) ? ' selected' : '' ?>>
                        <?= htmlReady($faculty->name) ?>
                    </option>
                    <?php foreach ($faculty->sub_institutes as $institute) { ?>
                        <option value="<?= $institute->Institut_id ?>"<?= ($flash['institute_id'] == $institute->Institut_id) ? ' selected' : '' ?>>
                            &nbsp;&nbsp;<?= htmlReady($institute->name) ?>
                        </option>
                    <?php } ?>
                <?php } ?>
            </select>
        </label>
    </section>
    <br>
    <section id="config">&nbsp;</section>
</form>
<script type="text/javascript">
//<!--
    STUDIP.Garuda.configInit();
//-->
</script>
