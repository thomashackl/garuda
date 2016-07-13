<?php

class RestrictedGenderFilterField extends GenderFilterField
{
    /**
     * Gets all users with given gender.
     *
     * @return Array All users that are affected by the current condition
     * field.
     */
    public function getUsers($restrictions = array())
    {
        // Get Garuda configuration...
        $config = GarudaModel::getConfigurationForUser($GLOBALS['user']->id);

        $users = DBManager::get()->fetchFirst("SELECT DISTINCT `user_id` " .
            "FROM `" . $this->userDataDbTable . "` " .
            "LEFT JOIN `user_studiengang` USING (`user_id`) " .
            "LEFT JOIN `abschluss` USING (`abschluss_id`) " .
            "LEFT JOIN `user_inst` USING (`user_id`) " .
            "WHERE `" . $this->userDataDbField . "`" . $this->compareOperator . " :value " .
            "AND ((`user_studiengang`.`studiengang_id` IN (:stg) " .
            "AND `user_studiengang`.`abschluss_id` IN (:degree)) " .
            "OR (`user_inst`.`Institut_id` IN (:inst) AND `user_inst`.`inst_perms` IN ('autor', 'tutor', 'dozent'))) " .
            "AND (`user_studiengang`.`user_id` IS NOT NULL OR `user_inst`.`user_id` IS NOT NULL)",
            array(
                'value' => $this->value,
                'stg' => array_map(function ($s) { return $s['studiengang_id']; }, $config['studycourses']),
                'degree' => array_map(function ($s) { return $s['abschluss_id']; }, $config['studycourses']),
                'inst' => $config['institutes'],
            ));

        return $users;
    }
}
