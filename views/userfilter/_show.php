<div class="userfilter" id="<?= $filter->getId() ?>">
    <?= $filter->toString() ?>
    <input type="hidden" name="filters[]" value="<?= htmlReady(serialize($filter)) ?>"/>
    <span class="actions">
        <a class="delete" href="<?= $controller->url_for('userfilter/delete', $filter->getId()) ?>"
                onclick="return STUDIP.Garuda.removeFilter(this)"
                title="<?= dgettext('garudaplugin', 'löschen') ?>">
            <?= Icon::create('trash', 'clickable'); ?></a>
    </span>
</div>
