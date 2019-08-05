<?php

class PermissionFilterField extends UserFilterField
{
    public $userDataDbField = 'inst_perms';
    public $userDataDbTable = 'user_inst';

    public function __construct($fieldId='')
    {
        $this->validCompareOperators = array(
            '='   => dgettext('garuda', 'ist'),
            '!=' => dgettext('garuda', 'ist nicht'),
        );

        $this->validValues = array(
            'autor' => dgettext('garuda', 'Mitglied (Autor/in)'),
            'tutor' => dgettext('garuda', 'Tutor/in'),
            'dozent' => dgettext('garuda', 'Lehrende/r'),
            'admin' => dgettext('garuda', 'Administrator/in')
        );

        if ($fieldId) {
            $this->id = $fieldId;
            $this->load();
        } else {
            $this->id = $this->generateId();
        }
    }

    public function getName()
    {
        return dgettext('garuda', 'Rechtestufe');
    }

    /**
     * Gets all users with given permission level.
     *
     * @return Array All users that are affected by the current condition
     * field.
     */
    public function getUsers($restrictions = array())
    {
        $users = DBManager::get()->fetchFirst("SELECT `user_id` " .
            "FROM `" . $this->userDataDbTable . "` " .
            "WHERE `" . $this->userDataDbField . "`" . $this->compareOperator .
            "?", array($this->value));

        return $users;
    }
}
