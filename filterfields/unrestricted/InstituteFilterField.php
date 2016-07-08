<?php

/**
 * InstituteFilterField.class.php
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
 * @category    Garuda
 */

class InstituteFilterField extends UserFilterField
{
    // --- ATTRIBUTES ---
    public $valuesDbTable = 'Institute';
    public $valuesDbIdField = 'Institut_id';
    public $valuesDbNameField = 'Name';
    public $userDataDbTable = 'user_inst';
    public $userDataDbField = 'Institut_id';

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
            '=' => dgettext('garudaplugin', 'ist'),
            '!=' => dgettext('garudaplugin', 'ist nicht')
        );
        $this->validValues = array();
        // Get all available institutes from database, grouped by faculty.
        $institutes = Institute::getInstitutes();
        foreach ($institutes as $i) {
            $this->validValues[$i[$this->valuesDbIdField]] = $i[$this->valuesDbNameField];
			if ($i['is_fak']) {
	            $this->validValues[$i[$this->valuesDbIdField].'_children'] = 
	            	sprintf(dgettext('garudaplugin', '%s und Untereinrichtungen'), 
	            	$i[$this->valuesDbNameField]);
			}
        }
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
        return dgettext('garudaplugin', "Einrichtung");
    }

    /**
     * Gets all users given to the currently selected institute.
     * 
     * @return Array All users that are affected by the current condition 
     * field.
     */
    public function getUsers($restrictions=array()) {
    	if (strpos($this->value, '_children') !== false) {
    		$realValue = substr($this->value, 0, strpos($this->value, '_children'));
    		$users = DBManager::get()->fetchFirst("SELECT `user_id` FROM `".
    			$this->userDataDbTable."` WHERE `".$this->userDataDbField.
    			"` IN (SELECT `".$this->userDataDbField."` FROM `".
    			$this->valuesDbTable."` WHERE `fakultaets_id`=?)", 
    			array($realValue));
		} else {
            $users = DBManager::get()->fetchFirst("SELECT `user_id` ".
                "FROM `".$this->userDataDbTable."` ".
                "WHERE `".$this->userDataDbField."`".$this->compareOperator.
                "?", array($this->value));
		}
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

} /* end of class InstituteFilterField */
