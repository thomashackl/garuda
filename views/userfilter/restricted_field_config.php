<select name="compare_operator[]" class="conditionfield_compare_op">
    <?php foreach ($field->getValidCompareOperators() as $op => $text) { ?>
    <option value="<?= $op ?>"<?= ($op == $field->getCompareOperator() ? ' selected' : '') ?>><?= htmlReady($text) ?></option>
    <?php } ?>
</select>
<select name="value[]" class="conditionfield_value" onchange="STUDIP.Garuda.getFieldConfig(this)">
    <option value="">
        <?= dgettext('garuda', 'alle') ?>
    </option>
    <?php foreach ($field->getValidValues() as $id => $name) { ?>
    <option value="<?= $id ?>"<?= ($id === $field->getValue() ? ' selected' : '') ?>>
        <?= htmlReady($name) ?>
    </option>
    <?php } ?>
</select>
<script type="text/javascript">
    //<!--
    STUDIP.Garuda.initField();
    //-->
</script>
