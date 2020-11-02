<select name="compare_operator[]" class="conditionfield_compare_op">
    <?php foreach ($field->getValidCompareOperators() as $op => $text) { ?>
    <option value="<?= $op ?>"><?= htmlReady($text) ?></option>
    <?php } ?>
</select>
<?
$values = $field->getValidValues();
?>
<? if (count($values)) : ?>
    <select name="value[]" class="conditionfield_value">
        <? foreach ($field->getValidValues() as $id => $name) : ?>
            <option value="<?= $id ?>"><?= htmlReady($name) ?></option>
        <? endforeach ?>
    </select>
    <? if ($field instanceof ValueInputFilterField) : ?>
        <? $input_field_data = $field->getValueInputFieldData() ?>
        <? foreach ($input_field_data as $id => $data) : ?>
            <? if ($data['type'] == 'select') : ?>
                <label>
                    <?= dgettext('garuda', 'mit Wert') ?>
                    <select name="value_input_<?= htmlReady($id)?>">
                        <option value="">(<?= dgettext('garuda', 'bitte wählen') ?>)</option>
                        <? foreach ($data['values'] as $value => $text) : ?>
                            <option value="<?= htmlReady($value)?>">
                                <?= htmlReady($text) ?>
                            </option>
                        <? endforeach ?>
                    </select>
                </label>
            <? elseif ($data['type'] == 'bool') : ?>
                <input type="hidden" name="value_input_<?= htmlReady($id)?>"
                       value="0">
                <label>
                    <input type="checkbox" name="value_input_<?= htmlReady($id)?>"
                           value="1">
                    <?= dgettext('garuda', 'Feld ist gesetzt') ?>
                </label>
            <? else : ?>
                <label>
                    <?= dgettext('garuda', 'mit Wert') ?>
                    <input type="text" name="value_input_<?= htmlReady($id)?>">
                </label>
            <? endif ?>
        <? endforeach ?>
    <? endif ?>
<? else : ?>
    <input name="value[]" size="40" class="conditionfield_value" value=""/>
<? endif ?>
<script type="text/javascript">
    //<!--
    STUDIP.Garuda.initField();
    //-->
</script>
