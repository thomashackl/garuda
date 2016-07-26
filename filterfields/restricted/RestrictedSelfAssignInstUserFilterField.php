<?php

/**
 * RestrictedSelfAssignUserInstFilterField.class.php
 *
 * People who have assigned themselves to some institute(s).
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

class RestrictedSelfAssignInstUserFilterField extends SelfAssignInstUserFilterField
{

    public $config = array();

    /**
     * @see SelfAssignInstUserFilterField::__construct
     */
    public function __construct($fieldId='', $restriction=array()) {
        parent::__construct($fieldId);
        $this->relations = array(
            'RestrictedSelfAssignInstUserFilterField' => array(
                'local_field' => 'range_id',
                'foreign_field' => 'Institut_id'
            )
        );

        // Get Garuda configuration:
        // Find out which user this filter belongs to...
        $filter = GarudaFilter::findByFilter_id($this->conditionId);
        // ... and load Garuda config for this user.
        $this->config = GarudaModel::getConfigurationForUser($filter->user_id);

        foreach ($this->validValues as $id => $name) {
            if (strpos($id, '_children') !== false) {
                $realId = substr($id, 0, strpos($id, '_children'));
            } else {
                $realId = $id;
            }
            if (!in_array($realId, array_keys($this->config['institutes']))) {
                unset($this->validValues[$id]);
            }
        }
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
                $this->valuesDbTable."` WHERE `fakultaets_id`".$this->compareOperator.
                "? AND `Institut_id` IN (?) AND `inst_perms` = 'user')",
                array($realValue, array_keys($this->config['institutes'])));
        } else {
            $users = DBManager::get()->fetchFirst("SELECT `user_id` ".
                "FROM `user_inst` ".
                "INNER JOIN `Institute` ON (`user_inst`.`Institut_id`=`Institute`.`Institut_id`) ".
                "WHERE `user_inst`.`Institut_id`".$this->compareOperator.
                "? AND `user_inst`.`inst_perms` = 'user'", array($this->value));
        }
        return $users;
    }

}
