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
            if ($current[$this->valuesDbIdField]) {
                $this->validValues[$current[$this->valuesDbIdField]] = $current[$this->valuesDbNameField];
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
        return _("Statusgruppe");
    }

    /**
     * Compares all the users' values by using the specified compare operator
     * and returns all users that fulfill the condition. This can be
     * an important information when checking on validity of a combination
     * of conditions.
     * 
     * @param Array $restrictions values from other fields that restrict the valid
     *                            values for a user (e.g. a semester of study in
     *                            a given subject)
     * @return Array All users that are affected by the current condition 
     *               field.
     */
    public function getUsers($restrictions=array()) {
        $db = DBManager::get();
        $users = array();
        // Standard query getting the values without respecting other values.
        $select = "SELECT DISTINCT `statusgruppe_user`.`user_id` ";
        $from = "FROM `statusgruppe_user` ";
        $from .= " INNER JOIN `statusgruppen` ON (`statusgruppen`.`statusgruppe_id`=`statusgruppe_user`.`statusgruppe_id`)";
        $from .= " INNER JOIN `Institute` ON (`statusgruppen`.`range_id`=`Institute`.`Institut_id`)";
        $where = "WHERE `statusgruppen`.`name`".$this->compareOperator."?";
        $parameters = array($this->value);
        $joinedTables = array(
            'statusgruppe_user' => true,
            'statusgruppen' => true,
            'Institute' => true
        );
        // Check if there are restrictions given.
        foreach ($restrictions as $otherField => $restriction) {
            // We only take the value into consideration if it represents a valid restriction.
            if ($this->relations[$otherField]) {
                // Do we need to join in another table?
                if (!$joinedTables[$restriction['table']]) {
                    $joinedTables[$restriction['table']] = true;
                    $from .= " INNER JOIN `".$restriction['table']."` ON (`".
                        $this->valuesDbTable."`.`".
                        $this->relations[$otherField]['local_field']."`=`".
                        $restriction['table']."`.`".
                        $this->relations[$otherField]['foreign_field']."`)";
                }
                // Expand WHERE statement with the value from restriction.
                $where .= " AND `".$restriction['table']."`.`".
                    $restriction['field']."`".$restriction['compare']."?";
                $parameters[] = $restriction['value'];
            }
        }
        // Get all the users that fulfill the condition.
        $stmt = $db->prepare($select.$from.$where);
        $stmt->execute($parameters);
        while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $current['user_id'];
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

} /* end of class StatusgroupFilterField */

?>