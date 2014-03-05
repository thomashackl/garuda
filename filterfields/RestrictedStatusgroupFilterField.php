<?php

/**
 * RestrictedStatusgroupMemberFilterField.class.php
 * 
 * People belonging to a given status group.
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

class RestrictedStatusgroupFilterField extends StatusgroupFilterField
{
    // --- ATTRIBUTES ---
    public $valuesDbTable = 'statusgruppen';
    public $valuesDbIdField = 'statusgruppe_id';
    public $valuesDbNameField = 'name';
    public $userDataDbTable = 'statusgruppe_user';
    public $userDataDbField = 'statusgruppe_id';

    public $config = array();

    /**
     * Standard constructor.
     */
    public function __construct($fieldId='', $restriction=array()) {
        $this->relations = array(
            'InstituteFilterField' => array(
                'local_field' => 'range_id',
                'foreign_field' => 'Institut_id'
            )
        );
        $this->validCompareOperators = array(
            '=' => dgettext('garudaplugin', 'gleich'),
            '!=' => dgettext('garudaplugin', 'ungleich')
        );
        $this->validValues = array();
        if ($restriction['value']) {
            switch ($restriction['compare']) {
                case '!=':
                    $compare = '!=';
                    break;
                case '=':
                default:
                    $compare = '==';
            }
        }
        // Get Garuda configuration...
        $this->config = GarudaModel::getConfigurationForUser($GLOBALS['user']->id);
        $groups = DBManager::get()->fetchAll("SELECT DISTINCT `name`, `range_id` FROM `statusgruppen` WHERE `range_id` IN (?)", array(array_keys($this->config['institutes'])));
        // Check if faculty level with sub institutes has been selected.
        if (strpos($restriction['value'], '_children') !== false) {
            $realValue = substr($restriction['value'], 0, strpos($restriction['value'], '_children'));
            $insts = DBManager::get()->fetchFirst("SELECT `Institut_id` FROM `Institute` WHERE `fakultaets_id`".$restriction['compare']."? AND `Institut_id` IN (?)", array($realValue, array_keys($this->config['institutes'])));
        }
        foreach ($groups as $g) {
            if ($g['name']) {
                if (strpos($restriction['value'], '_children') !== false) {
                        $eval = in_array($g['range_id'], $insts);
                    } else {
                        $eval = eval("return ('".$g['range_id']."'".$compare."'".$restriction['value']."');");
                    }
                if (!$realValue || $eval) {
                    $this->validValues[$g['name']] = $g['name'];
                }
            }
        }
        natcasesort($this->validValues);
        $this->validValues = array('' => dgettext('garudaplugin', 'alle')) + $this->validValues;
        if ($fieldId) {
            $this->id = $fieldId;
            $this->load();
        } else {
            $this->id = $this->generateId();
        }
    }

    /**
     * Get this field's display name.
     *
     * @return String
     */
    public function getName()
    {
        return dgettext('garudaplugin', "Statusgruppe");
    }

    /**
     * Gets all institute assignments for the given user.
     * 
     * @param  String $userId User to check.
     * @param  Array additional conditions that are required for check.
     * @return The value(s) for this user.
     */
    public function getUserValues($userId, $additional=null) {
        $result = array();
        // Get institute memberships for user.
        $result = array_map(function($i) {
            return $i->Institut_id;
        }, InstituteMember::findByUser($userId));
        return $result;
    }

} /* end of class RestrictedStatusgroupMemberFilterField */

?>