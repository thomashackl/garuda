<?php

/**
 * RestrictedInstituteMemberFilterField.class.php
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

class RestrictedInstituteMemberFilterField extends UserFilterField
{
    // --- ATTRIBUTES ---
    public $valuesDbTable = 'Institute';
    public $valuesDbIdField = 'Institut_id';
    public $valuesDbNameField = 'Name';
    public $userDataDbTable = 'user_inst';
    public $userDataDbField = 'Institut_id';

    /**
     * @see UserFilterField::_construct
     */
    public function __construct($fieldId='', $valueRestriction='') {
        parent::__construct();
        $this->config = GarudaModel::getConfigurationForUser($GLOBALS['user']->id);
        $institutes = array_keys($this->config);
        foreach ($institutes as $institute) {
            $this->validValues[$institute->Institut_id] = $institute->name;
        }
    }

    /**
     * Get this field's display name.
     *
     * @return String
     */
    public function getName()
    {
        return _("Einrichtung");
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

} /* end of class RestrictedInstituteMemberFilterField */

?>