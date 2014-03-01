<?php

/**
 * StatusgroupFilterField.class.php
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

class StatusgroupFilterField extends UserFilterField
{
    // --- ATTRIBUTES ---
    public $valuesDbTable = 'statusgruppen';
    public $valuesDbIdField = 'name';
    public $valuesDbNameField = 'name';
    public $userDataDbTable = 'statusgruppe_user';
    public $userDataDbField = 'statusgruppe_id';

    /**
     * @see UserFilterField::__construct
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
        // Get all available institute statusgroups from database.
        $stmt = DBManager::get()->query(
            "SELECT DISTINCT s.`".$this->valuesDbIdField."` ".
            "FROM `".$this->valuesDbTable."` s ".
            "INNER JOIN `Institute` i ON (s.`range_id`=i.`Institut_id`) ".
            "ORDER BY s.`".$this->valuesDbNameField."` ASC");
        while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->validValues[$current[$this->valuesDbIdField]] = $current[$this->valuesDbNameField];
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
        return _("Statusgruppe");
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

} /* end of class StatusgroupFilterField */

?>