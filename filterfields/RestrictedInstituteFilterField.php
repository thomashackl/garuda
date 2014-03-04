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
    public function __construct($fieldId='', $restriction=array()) {
        $this->validValues = array();
        parent::__construct($fieldId);
        // Get Garuda configuration...
        $config = GarudaModel::getConfigurationForUser($GLOBALS['user']->id);
        // Get legal values for institutes according to statusgroup name restriction.
        $groupRanges = array();
        if ($restriction['value']) {
            switch ($restriction['compare']) {
                case '!=':
                    $compare = '!=';
                    break;
                case '=':
                default:
                    $compare = '=';
            }
            $groupRanges = DBManager::get()->fetchFirst(
                "SELECT DISTINCT `range_id` FROM `statusgruppen` WHERE `name`".
                    $compare."?", array($restriction['value']));
            
        }
        foreach ($this->validValues as $id => $name) {
            if (!in_array($id, array_keys($config['institutes']))) {
                unset($this->validValues[$id]);
            }
            if ($groupRanges) {
                if (!in_array($id, $groupRanges)) {
                    unset($this->validValues[$id]);
                }
            }
        }
        $this->validValues = array('' => _('alle')) + $this->validValues;
    }

} /* end of class RestrictedInstituteFilterField */

?>