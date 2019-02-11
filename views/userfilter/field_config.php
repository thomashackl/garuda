<select name="compare_operator[]" class="conditionfield_compare_op">
    <?php foreach ($field->getValidCompareOperators() as $op => $text) { ?>
    <option value="<?= $op ?>"><?= htmlReady($text) ?></option>
    <?php } ?>
</select>
<?php
    $values = $field->getValidValues();
    if (count($values)) :
?>
<select name="value[]" class="conditionfield_value">
    <?php foreach ($field->getValidValues() as $id => $name) { ?>
    <option value="<?= $id ?>"><?= htmlReady($name) ?></option>
    <?php } ?>
</select>
<?php else : ?>
<input name="value[]" size="40" class="conditionfield_value" value=""/>
<? endif ?>
<script type="text/javascript">
    //<!--
    STUDIP.Garuda.initField();
    //-->
</script>
