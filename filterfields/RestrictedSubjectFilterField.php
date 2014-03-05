<?php

/**
 * RestrictedSubjectFilterField.class.php
 * 
 * All conditions concerning the subject of study in Stud.IP can be specified here.
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

require_once('lib/classes/admission/userfilter/SubjectCondition.class.php');

class RestrictedSubjectFilterField extends SubjectCondition
{
    /**
     * Standard constructor.
     */
    public function __construct($fieldId='', $restriction=array()) {
        parent::__construct($fieldId);
        $this->validValues = array(
            '' => dgettext('garudaplugin', 'alle')
        );
        $this->config = GarudaModel::getConfigurationForUser($GLOBALS['user']->id);
        if ($restriction['compare'] == '=') {
            $restriction['compare'] = '==';
        }
        foreach($this->config['studycourses'] as $entry) {
            if (!$restriction['value'] || ($restriction && eval("return ('".$entry['abschluss_id']."'".$restriction['compare']."'".$restriction['value']."');"))) {
                $s = new Studycourse($entry['studiengang_id']);
                $this->validValues[$entry['studiengang_id']] = $s->name;
            }
        }
    }

    /**
     * Get this field's display name.
     *
     * @return String
     */
    public function getName()
    {
        return dgettext('garudaplugin', "Studienfach");
    }

    /**
     * Gets the users affected by the current filter field. If 'all' has been
     * set as filter value, we "trick" the SQL by injecting an array of all
     * allowed values. 
     * 
     * @param Array $restrictions values from other fields that restrict the valid
     *                            values for a user (e.g. a semester of study in
     *                            a given subject)
     * @return Array All users that are affected by the current condition 
     *               field.
     */
    public function getUsers($restrictions=array()) {
        if ($this->value == 'all') {
            $this->compareOperator = ' IN ';
            $this->value = "('".implode("', '", array_keys($this->validValues))."')";
        }
        return parent::getUsers($restrictions);
    }

    /**
     * Gets the value for the given user that is relevant for this
     * condition field. Here, this method looks up the study degree(s) 
     * for the user. These can then be compared with the required degrees
     * whether they fit.
     * 
     * @param  String $userId User to check.
     * @param  Array additional conditions that are required for check.
     * @return The value(s) for this user.
     */
    public function getUserValues($userId, $additional=null) {
        $result = array();
        // Get degrees for user.
        $stmt = DBManager::get()->prepare(
            "SELECT DISTINCT `studiengang_id` ".
            "FROM `user_studiengang` ".
            "WHERE `user_id`=?");
        $stmt->execute(array($userId));
        while ($current = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $current['abschluss_id'];
        }
        return $result;
    }

} /* end of class RestrictedSubjectFilterField */

?>