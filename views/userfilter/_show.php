<div class="userfilter" id="<?= $filter->getId() ?>">
    <?= $filter->toString() ?>
    <input type="hidden" name="filters[]" value="<?= htmlReady(serialize($filter)) ?>"/>
    <span class="userfilter_actions">
        <a class="delete" href="<?= $controller->url_for('userfilter/delete', $filter->getId()) ?>" title="<?= _('löschen') ?>"><?= Assets::img('icons/16/blue/trash.png'); ?></a>
    </span>
</div>