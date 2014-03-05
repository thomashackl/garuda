<select name="compare_operator[]" size="1" class="conditionfield_compare_op">
    <?php foreach ($field->getValidCompareOperators() as $op => $text) { ?>
    <option value="<?= $op ?>"<?= ($op == $field->getCompareOperator() ? ' selected="selected"' : '') ?>><?= htmlReady($text) ?></option>
    <?php } ?>
</select>
<select name="value[]" size="1" class="conditionfield_value" onchange="STUDIP.Garuda.getFieldConfig(this)">
    <?php foreach ($field->getValidValues() as $id => $name) { ?>
    <option value="<?= $id ?>"<?= ($id == $field->getValue() ? ' selected="selected"' : '') ?>><?= htmlReady($name) ?></option>
    <?php } ?>
</select>
<script type="text/javascript">
    //<!--
    STUDIP.Garuda.initField();
    //-->
</script>
