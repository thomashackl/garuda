<?php


/**
 * This is a specialisation of the UserFilterField class
 * for filters that allow users to enter a value as additional filter.
 */
abstract class ValueInputFilterField extends UserFilterField
{
    protected $value_input_field_data = [];


    public function getValueInputFieldData() : array
    {
        return $this->value_input_field_data;
    }
}
