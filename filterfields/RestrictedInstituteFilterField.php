<?php

/**
 * RestrictedInstituteFilterField.class.php
 * 
 * People belonging to a given institute.
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class RestrictedInstituteFilterField extends InstituteFilterField
{

    /**
     * @see UserFilterField::__construct
     */
    public function __construct($fieldId='') {
        $this->relations = array(
            'StatusgroupFilterField' => array(
                'local_field' => 'Institut_id',
                'foreign_field' => 'range_id'
            )
        );
        $this->validCompareOperators = array(
            '=' => _('gleich'),
            '!=' => _('ungleich')
        );
        // Get Garuda configuration...
        $config = GarudaModel::getConfigurationForUser($GLOBALS['user']->id);
        foreach ($config['institutes'] as $i) {
            $this->validValues[$i[$this->valuesDbIdField]] = $i[$this->valuesDbNameField];
            if ($i['Institut_id'] == $i['fakultaets_id']) {
                $this->validValues[$i[$this->valuesDbIdField].'_children'] = sprintf(_('%s und untergeordnete Einrichtungen'), $i[$this->valuesDbNameField]);
            }
        }
        if ($fieldId) {
            $this->id = $fieldId;
            $this->load();
        } else {
            $this->id = $this->generateId();
        }
    }

} /* end of class RestrictedInstituteFilterField */

?>