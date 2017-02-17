<?php

/**
 * RestrictedSemesterOfStudyFilterField.class.php
 *
 * All conditions concerning the semester of study in Stud.IP can be specified here.
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

class RestrictedSemesterOfStudyFilterField extends SemesterOfStudyCondition
{

    public $config = array();

    // --- OPERATIONS ---

    /**
     * @see UserFilterField::__construct
     */
    public function __construct($fieldId='') {
        parent::__construct($fieldId);

        // Get Garuda configuration:
        // Find out which user this filter belongs to...
        $filter = GarudaFilter::findByFilter_id($this->conditionId);
        // ... and load Garuda config for this user.
        $this->config = GarudaModel::getConfigurationForUser($filter->user_id ?: $GLOBALS['user']->id);

        $this->validValues = array();
        // Initialize to some value in case there are no semester numbers.
        $maxsem = 15;
        $where = "";
        $parameters = array();
        foreach ($this->config['studycourses'] as $entry) {
            if ($where) {
                $where .= " OR ";
            }
            $where .= "(`abschluss_id`=? AND `studiengang_id`=?)";
            $parameters[] = $entry['abschluss_id'];
            $parameters[] = $entry['studiengang_id'];
        }
        if ($where) {
            $where = "AND (".$where.")";
        }
        // Calculate the maximal available semester.
        $data = DBManager::get()->fetchFirst("SELECT MAX(".$this->valuesDbIdField.") AS maxsem ".
            "FROM `".$this->valuesDbTable."` WHERE 1 ".$where, $parameters);
        if ($data[0]) {
            $maxsem = $data[0];
        }
        for ($i=1 ; $i<=$maxsem ; $i++) {
            $this->validValues[$i] = $i;
        }
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
        $select = "SELECT DISTINCT `".$this->userDataDbTable."`.`user_id` ";
        $from = "FROM `".$this->userDataDbTable."` ";
        $where = "WHERE `".$this->userDataDbTable."`.`".$this->userDataDbField.
            "`".$this->compareOperator."?";
        $parameters = array($this->value);
        $joinedTables = array(
            $this->userDataDbTable => true
        );
        // Join in allowed values from Garuda config.
        $allowed .= "";
        foreach ($this->config['studycourses'] as $entry) {
            if ($allowed) {
                $allowed .= " OR ";
            }
            $allowed .= "(`abschluss_id`=? AND `studiengang_id`=?)";
            $parameters[] = $entry['abschluss_id'];
            $parameters[] = $entry['studiengang_id'];
        }
        $where .= " AND (".$allowed.")";
        // Check if there are restrictions given.
        foreach ($restrictions as $otherField => $restriction) {
            // We only take the value into consideration if it represents a valid restriction.
            if ($this->relations[$otherField]) {
                // Do we need to join in another table?
                if (!$joinedTables[$restriction['table']]) {
                    $joinedTables[$restriction['table']] = true;
                    $from .= " INNER JOIN `".$restriction['table']."` ON (`".
                        $this->userDataDbTable."`.`".
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

} /* end of class RestrictedSemesterOfStudyFilterField */
