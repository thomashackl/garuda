<?php

class RestrictedPermissionFilterField extends PermissionFilterField
{
    public $config = array();

    /**
     * Gets all users with given permission level.
     *
     * @return Array All users that are affected by the current condition
     * field.
     */
    public function getUsers($restrictions = array())
    {
        // Get Garuda configuration:
        // Find out which user this filter belongs to...
        $filter = GarudaFilter::findOneByFilter_id($this->conditionId);
        // ... and load Garuda config for this user.
        $this->config = GarudaModel::getConfigurationForUser($filter->user_id ?: $GLOBALS['user']->id);

        $users = DBManager::get()->fetchFirst("SELECT `user_id` " .
            "FROM `" . $this->userDataDbTable . "` " .
            "WHERE `" . $this->userDataDbField . "`" . $this->compareOperator .
            "? AND `Institut_id` IN (?)",
            array($this->value, $this->config['institutes']));

        return $users;
    }
}
