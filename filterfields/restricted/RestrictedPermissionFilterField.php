<?php

class RestrictedPermissionFilterField extends PermissionFilterField
{
    /**
     * Gets all users with given permission level.
     *
     * @return Array All users that are affected by the current condition
     * field.
     */
    public function getUsers($restrictions = array())
    {
        // Get Garuda configuration...
        $config = GarudaModel::getConfigurationForUser($GLOBALS['user']->id);

        $users = DBManager::get()->fetchFirst("SELECT `user_id` " .
            "FROM `" . $this->userDataDbTable . "` " .
            "WHERE `" . $this->userDataDbField . "`" . $this->compareOperator .
            "? AND `Institut_id` IN (?)",
            array($this->value, $config['institutes']));

        return $users;
    }
}
