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

    /**
     * Standard constructor.
     */
    public function __construct($fieldId='') {
        $this->relations = array(
            'InstituteFilterField' => array(
                'local_field' => 'range_id',
                'foreign_field' => 'Institut_id'
            )
        );
        $this->validCompareOperators = array(
            '=' => _('gleich'),
            '!=' => _('ungleich')
        );
        // Get Garuda configuration...
        $config = GarudaModel::getConfigurationForUser($GLOBALS['user']->id);
        $groups = DBManager::get()->fetchAll("SELECT DISTINCT `name` FROM `statusgruppen` WHERE `range_id` IN (?)", array(array_map(function($i) {
                return $i['id'];
            }, $config['institutes'])));
        foreach ($groups as $g) {
            if ($g['name']) {
                $this->validValues[$g['name']] = $g['name'];
            }
        }
        natcasesort($this->validValues);
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
        return _("Statusgruppe");
    }

    /**
     * Gets all users given to the currently selected institute.
     * 
     * @return Array All users that are affected by the current condition 
     * field.
     */
    public function getUsers() {
        $users = array_map(function($u) {
            return $u->user_id;
        }, InstituteMember::findByInstitute($this->value));
        return $users;
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