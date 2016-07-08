<?php

class PermissionFilterField extends UserFilterField
{
    public $userDataDbField = 'inst_perms';
    public $userDataDbTable = 'user_inst';

    public function __construct($fieldId='')
    {
        $this->validCompareOperators = array(
            '='   => _('ist'),
            '!=' => _('ist nicht'),
        );

        $this->validValues = array(
            'user' => dgettext('garudaplugin', 'Selbst zugeordnet (Leser/in)'),
            'autor' => dgettext('garudaplugin', 'Mitglied (Autor/in)'),
            'tutor' => dgettext('garudaplugin', 'Tutor/in'),
            'dozent' => dgettext('garudaplugin', 'Lehrende/r'),
            'admin' => dgettext('garudaplugin', 'Administrator/in')
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
        return _('Rechtestufe');
    }

    public static function getFilterName()
    {
        return _('Rechtestufe');
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
