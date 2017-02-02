<?php

class RestrictedGenderFilterField extends GenderFilterField
{
    public $config = array();

    /**
     * Gets all users with given gender.
     *
     * @return Array All users that are affected by the current condition
     * field.
     */
    public function getUsers($restrictions = array())
    {
        // Get Garuda configuration:
        // Find out which user this filter belongs to...
        $filter = GarudaFilter::findByFilter_id($this->conditionId);
        // ... and load Garuda config for this user.
        $this->config = GarudaModel::getConfigurationForUser($filter->user_id);

        $users = DBManager::get()->fetchFirst("SELECT DISTINCT `user_id` " .
            "FROM `" . $this->userDataDbTable . "` " .
            "LEFT JOIN `user_studiengang` USING (`user_id`) " .
            "LEFT JOIN `abschluss` USING (`abschluss_id`) " .
            "LEFT JOIN `user_inst` USING (`user_id`) " .
            "WHERE `" . $this->userDataDbField . "`" . $this->compareOperator . " :value " .
            "AND ((`user_studiengang`.`fach_id` IN (:stg) " .
            "AND `user_studiengang`.`abschluss_id` IN (:degree)) " .
            "OR (`user_inst`.`Institut_id` IN (:inst) AND `user_inst`.`inst_perms` IN ('autor', 'tutor', 'dozent'))) " .
            "AND (`user_studiengang`.`user_id` IS NOT NULL OR `user_inst`.`user_id` IS NOT NULL)",
            array(
                'value' => $this->value,
                'stg' => array_map(function ($s) { return $s['fach_id']; }, $config['studycourses']),
                'degree' => array_map(function ($s) { return $s['abschluss_id']; }, $config['studycourses']),
                'inst' => $this->config['institutes'],
            ));

        return $users;
    }
}
