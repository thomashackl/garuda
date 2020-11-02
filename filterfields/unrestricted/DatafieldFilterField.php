<?php


class DatafieldFilterField extends ValueInputFilterField
{
    protected $datafields = [];


    protected $value_input_field_data = [];


    /**
     * Retrieves the IDs and names of all datafields
     * that are defined for users and sets up all the
     * relevant class attributes.
     */
    protected function setupDatafields()
    {
        $this->datafields = DataField::findBySql(
            "`object_type` = 'user' ORDER BY `name` ASC"
        );
        $this->validValues = [];
        $this->value_input_field_data = [];
        if ($this->datafields) {
            foreach ($this->datafields as $field) {
                $this->validValues[$field->id] = $field->name;
                //Setup the value input fields for each datafield.
                //Since datafields can have different types, we must
                //set them up for each type.
                $simplified_type = 'text';
                $field_values = [];
                var_dump($field->type);
                if (in_array($field->type, ['selectbox', 'selectboxmultiple', 'radio'])) {
                    $simplified_type = 'select';
                    //Get the values by using DataFieldEntry etc.
                    $entry = DataFieldEntry::createDataFieldEntry($field);
                    $field_values = $entry->getParameters()[0] ?: [];
                } elseif ($field->type == 'bool') {
                    $simplified_type = 'bool';
                }
                $this->value_input_field_data[$field->id] = [
                    'type' => $simplified_type,
                    'values' => $field_values
                ];
            }
        }
    }


    public function __construct($field_id = '')
    {
        $this->validCompareOperators = [
            '=' => dgettext('garuda', 'ist')
        ];

        $this->setupDatafields();
    }


    public function getName()
    {
        return dgettext('garuda', 'Datenfeld');
    }
}
